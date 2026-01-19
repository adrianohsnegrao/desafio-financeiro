<?php

namespace App\Http\Controllers;

use App\Domains\Transfer\Exceptions\TransferException;
use App\Domains\Transfer\Services\TransferService;
use App\Http\Requests\TransferCompatRequest;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $service
    ) {}

    public function store(TransferRequest $request): JsonResponse
    {
        try {
            $transfer = $this->service->execute(
                payerId: $request->payer_id,
                payeeId: $request->payee_id,
                amount: (float) $request->amount,
                idempotencyKey: $request->idempotency_key
            );

            return response()->json($transfer, 201);

        } catch (TransferException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function storeCompat(TransferCompatRequest $request): JsonResponse
    {
        try {
            $idempotencyKey = $request->input('idempotency_key') ?? (string) \Illuminate\Support\Str::uuid();

            $transfer = $this->service->execute(
                payerId: (int) $request->payer,
                payeeId: (int) $request->payee,
                amount: (float) $request->value,
                idempotencyKey: $idempotencyKey
            );

            return response()->json($transfer, 201);
        } catch (TransferException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
