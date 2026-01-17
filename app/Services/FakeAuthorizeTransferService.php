<?php

namespace App\Services;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;

class FakeAuthorizeTransferService implements AuthorizeTransferServiceInterface
{
    public function authorize(): bool
    {
        return true;
    }
}
