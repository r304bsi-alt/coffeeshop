<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['gudang', 'owner']) ?? false;
    }

    public function rules(): array
    {
        return [
            'received' => ['required', 'array', 'min:1'],
            'received.*' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'received.required' => 'Kuantitas barang yang diterima dari supplier wajib dicatat.',
            'received.*.numeric' => 'Jumlah barang masuk harus berupa angka yang valid.',
        ];
    }
}
