<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->select(['id', 'name', 'type', 'balance'])
            ->with([
                'transfersMade' => function ($query) {
                    $query->select([
                        'id',
                        'payer_id',
                        'payee_id',
                        'amount',
                        'status',
                        'idempotency_key',
                        'created_at',
                    ])->orderByDesc('created_at');
                },
            ])
            ->orderBy('id')
            ->get();

        $payload = $users->map(function (User $user) {
            return [
                'id' => $user->id,
                'type' => $user->type,
                'name' => $user->name,
                'balance' => $user->balance,
                'transfers' => $user->transfersMade->map(function ($transfer) {
                    return [
                        'id' => $transfer->id,
                        'payee_id' => $transfer->payee_id,
                        'amount' => $transfer->amount,
                        'status' => $transfer->status,
                        'idempotency_key' => $transfer->idempotency_key,
                        'created_at' => $transfer->created_at,
                    ];
                }),
            ];
        });

        return response()->json(['data' => $payload]);
    }
}
