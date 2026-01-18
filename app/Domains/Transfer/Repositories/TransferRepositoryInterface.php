<?php

namespace App\Domains\Transfer\Repositories;

use App\Models\Domains\Transfer\Models\Transfer;

interface TransferRepositoryInterface
{
    public function findByIdempotencyKey(string $idempotencyKey): ?Transfer;

    public function create(array $data): Transfer;
}
