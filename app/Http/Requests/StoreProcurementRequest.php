<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['pengadaan', 'owner']) ?? false;
    }

    public function rules(): array
    {
        return [
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_email' => ['nullable', 'email', 'max:255'],
            'supplier_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.quantity_requested' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_name.required' => 'Nama supplier wajib diisi.',
            'items.required' => 'Daftar barang pengadaan wajib diisi minimal 1 item.',
            'items.*.ingredient_id.required' => 'Bahan baku wajib dipilih.',
            'items.*.quantity_requested.required' => 'Jumlah kuantitas pengadaan wajib diisi.',
            'items.*.unit_price.required' => 'Estimasi harga per satuan wajib diisi.',
        ];
    }
}
