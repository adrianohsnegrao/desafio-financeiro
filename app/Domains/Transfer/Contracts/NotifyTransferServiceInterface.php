<?php

namespace App\Domains\Transfer\Contracts;

use App\Models\Domains\Transfer\Models\Transfer;

interface NotifyTransferServiceInterface
{
    public function notify(Transfer $transfer): void;
}
