<?php

namespace App\Http\Controllers;

use App\Domains\Transfer\Services\TransferService;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function store(
        TransferRequest $request,
        TransferService $service
    ) {
        $transfer = $service->execute(
            $request->payer_id,
            $request->payee_id,
            $request->amount
        );

        return response()->json($transfer, 201);
    }
}
