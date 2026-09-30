<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Anyone on table QR or authenticated customer
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'table_number' => ['required', 'string', 'max:50'],
            'order_type' => ['required', 'in:dine_in,take_away'],
            'payment_method' => ['required', 'in:cash,midtrans_qris'],
            'cash_tendered' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama pemesan wajib diisi.',
            'table_number.required' => 'Nomor meja wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih (QRIS Midtrans atau Tunai).',
            'items.required' => 'Keranjang pesanan tidak boleh kosong.',
            'items.*.menu_id.required' => 'Menu yang dipesan tidak valid.',
            'items.*.quantity.min' => 'Jumlah pesanan minimal 1 porsi.',
        ];
    }
}
