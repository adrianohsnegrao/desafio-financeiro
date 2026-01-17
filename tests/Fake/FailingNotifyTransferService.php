<?php

namespace Tests\Fake;

use App\Domains\Transfer\Contracts\NotifyTransferServiceInterface;
use App\Models\Domains\Transfer\Models\Transfer;

class FailingNotifyTransferService implements NotifyTransferServiceInterface
{
    public function notify(Transfer $transfer): void
    {
        throw new \Exception('Notification service down');
    }
}
