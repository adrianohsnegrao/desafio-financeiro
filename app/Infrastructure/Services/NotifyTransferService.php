<?php

namespace App\Infrastructure\Services;

use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Models\Domains\Transfer\Models\Transfer;
use Illuminate\Support\Facades\Http;

class NotifyTransferService implements NotifyTransferServiceInterface
{
    public function notify(Transfer $transfer): void
    {
        $response = Http::timeout(5)->post(
            config('services.transfer.notifier'),
            [
                'transfer_id' => $transfer->id,
                'amount' => $transfer->amount,
                'payer' => $transfer->payer_id,
                'payee' => $transfer->payee_id,
            ]
        );

        if (!$response->successful()) {
            throw new \RuntimeException('Notification failed');
        }
    }
}
