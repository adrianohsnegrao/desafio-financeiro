<?php

namespace App\Http\Requests;

class TransferCompatRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payer' => ['required', 'exists:users,id'],
            'payee' => ['required', 'exists:users,id', 'different:payer'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'idempotency_key' => ['sometimes', 'uuid'],
        ];
    }
}
