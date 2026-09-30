<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['kasir', 'owner']) ?? false;
    }

    public function rules(): array
    {
        return [
            'order_identifier' => ['required', 'string'], // order_number or payment_token
            'cash_tendered' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_identifier.required' => 'Nomor order atau kode QR bayar wajib dimasukkan.',
        ];
    }
}
