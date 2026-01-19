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
        $existing = $this->repository->findByIdempotencyKey($idempotencyKey);
        if ($existing) {
            return $existing;
        }

        if (! $this->authorizer->authorize()) {
            throw new UnauthorizedTransferException('Transfer not authorized');
        }

        return DB::transaction(function () use (
            $payerId,
            $payeeId,
            $amount,
            $idempotencyKey
        ) {

            $payer = User::where('id', $payerId)->lockForUpdate()->first();
            $payee = User::where('id', $payeeId)->lockForUpdate()->first();

            $existing = $this->repository->findByIdempotencyKey($idempotencyKey);
            if ($existing) {
                return $existing;
            }

            if ($payer->isMerchant()) {
                throw new UnauthorizedTransferException('Merchant cannot transfer funds');
            }

            if ($payer->balance < $amount) {
                throw new InsufficientBalanceException('Insufficient balance');
            }

            $payer->decrement('balance', $amount);
            $payee->increment('balance', $amount);

            $transfer = $this->repository->create([
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
