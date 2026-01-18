<?php

namespace App\Infrastructure\Persistence;

use App\Domains\Transfer\Repositories\TransferRepositoryInterface;
use App\Models\Domains\Transfer\Models\Transfer;

class EloquentTransferRepository implements TransferRepositoryInterface
{
    public function findByIdempotencyKey(string $idempotencyKey): ?Transfer
    {
        return Transfer::where('idempotency_key', $idempotencyKey)
            ->lockForUpdate()
            ->first();
    }

    public function create(array $data): Transfer
    {
        return Transfer::create($data);
    }
}
