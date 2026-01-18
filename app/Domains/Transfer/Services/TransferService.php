<?php

namespace App\Domains\Transfer\Services;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;
use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Domains\Transfer\Exceptions\InsufficientBalanceException;
use App\Domains\Transfer\Exceptions\UnauthorizedTransferException;
use App\Models\Domains\Transfer\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    public function __construct(
        private AuthorizeTransferServiceInterface $authorizer,
        private NotifyTransferServiceInterface $notifier
    ) {}
    public function execute(int $payerId, int $payeeId, float $amount, string $idempotencyKey): Transfer
    {
        return DB::transaction(function () use ($payerId, $payeeId, $amount, $idempotencyKey) {

            $existing = Transfer::where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            if (! $this->authorizer->authorize()) {
                throw new UnauthorizedTransferException('Transfer not authorized');
            }

            Log::info('Transfer started', compact('payerId', 'payeeId', 'amount'));

            $payer = User::lockForUpdate()->findOrFail($payerId);
            $payee = User::lockForUpdate()->findOrFail($payeeId);

            if ($payer->type === 'merchant') {
                Log::warning('Unauthorized transfer attempt', ['payer' => $payerId]);

                throw new UnauthorizedTransferException('Merchant cannot transfer funds');
            }

            if ($payer->balance < $amount) {
                Log::warning('Insufficient balance', ['payer' => $payerId]);
                throw new InsufficientBalanceException('Insufficient balance');
            }

            $payer->decrement('balance', $amount);
            $payee->increment('balance', $amount);

            $transfer = Transfer::create([
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
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

            Log::info('Transfer completed', ['transfer_id' => $transfer->id]);

            return $transfer;
        });
    }
}
