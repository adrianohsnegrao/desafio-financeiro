<?php

namespace App\Services;

use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Models\Domains\Transfer\Models\Transfer;
use Illuminate\Support\Facades\Log;

class FakeNotifyTransferService implements NotifyTransferServiceInterface
{
    public function notify(Transfer $transfer): void
    {
        Log::info('Fake notification sent', [
            'transfer_id' => $transfer->id
        ]);
    }
}
