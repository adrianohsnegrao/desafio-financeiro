<?php

namespace App\Domains\Transfer\Services;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Domains\Transfer\Exceptions\InsufficientBalanceException;
use App\Domains\Transfer\Exceptions\UnauthorizedTransferException;
use App\Domains\Transfer\Repositories\TransferRepositoryInterface;
use App\Models\Domains\Transfer\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    public function __construct(
        private AuthorizeTransferServiceInterface $authorizer,
        private NotifyTransferServiceInterface $notifier,
        private TransferRepositoryInterface $repository,
    ) {}
    public function execute(int $payerId, int $payeeId, float $amount, string $idempotencyKey): Transfer
    {

        if (! $this->authorizer->authorize()) {
            throw new UnauthorizedTransferException('Transfer not authorized');
        }

        $payer = User::findOrFail($payerId);
        $payee = User::findOrFail($payeeId);

        if ($payer->isMerchant()) {
            throw new UnauthorizedTransferException('Merchant cannot transfer funds');
        }

        if ($payer->balance < $amount) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        // 🟢 MUTAÇÃO DE ESTADO (DENTRO da transaction)
        return DB::transaction(function () use (
            $payer,
            $payee,
            $amount,
            $idempotencyKey
        ) {

            $existing = $this->repository->findByIdempotencyKey($idempotencyKey);

            if ($existing) {
                return $existing;
            }

            $payer = User::where('id', $payer->id)->lockForUpdate()->first();
            $payee = User::where('id', $payee->id)->lockForUpdate()->first();

            $payer->decrement('balance', $amount);
            $payee->increment('balance', $amount);

            $transfer = Transfer::create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'amount' => $amount,
                'status' => 'approved',
                'idempotency_key' => $idempotencyKey,
            ]);

            try {
                $this->notifier->notify($transfer);
            } catch (\Throwable $e) {
                Log::error('Notification failed', [
                    'transfer_id' => $transfer->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $transfer;
        });
    }
}
