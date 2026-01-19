<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class TransferCompatRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'payer.required' => 'Payer is required',
            'payee.required' => 'Payee is required',
            'value.min' => 'Value must be greater than zero',
        ];
    }
}
