<?php

namespace App\Domains\Transfer\Contracts;

interface AuthorizeTransferServiceInterface
{
    public function authorize(): bool;
}
