<?php

namespace Tests\Fake;

use App\Domains\Transfer\Contracts\AuthorizeTransferServiceInterface;

class DenyAuthorizeTransferService implements AuthorizeTransferServiceInterface
{
    public function authorize(): bool
    {
        return false;
    }

}
