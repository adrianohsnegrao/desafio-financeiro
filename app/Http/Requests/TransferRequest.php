<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payer_id' => ['required', 'exists:users,id'],
            'payee_id' => ['required', 'exists:users,id', 'different:payer_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'payer_id.required' => 'Payer is required',
            'payee_id.required' => 'Payee is required',
            'payee_id.different' => 'Payer and payee must be different',
            'amount.min' => 'Amount must be greater than zero',
            'idempotency_key.required' => 'Idempotency key is required',
        ];
    }
}
