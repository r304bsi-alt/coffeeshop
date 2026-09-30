<?php

namespace App\Http\Requests;

use App\Models\Stock;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['gudang', 'owner']) ?? false;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $items = $this->input('items', []);
            foreach ($items as $index => $item) {
                if (!isset($item['ingredient_id']) || !isset($item['quantity'])) {
                    continue;
                }
                $gudangStock = Stock::where('ingredient_id', $item['ingredient_id'])
                    ->where('location', 'gudang')
                    ->value('quantity') ?? 0;

                if ((float) $item['quantity'] > (float) $gudangStock) {
                    $v->errors()->add("items.{$index}.quantity", "Jumlah stok yang dikeluarkan melebihi stok yang tersedia di gudang (Tersedia: {$gudangStock}).");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Daftar item barang keluar wajib ditentukan minimal 1 item.',
            'items.*.ingredient_id.required' => 'Bahan baku wajib dipilih.',
            'items.*.quantity.required' => 'Jumlah barang keluar wajib diisi.',
            'items.*.quantity.min' => 'Jumlah barang keluar minimal 0.01.',
        ];
    }
}
