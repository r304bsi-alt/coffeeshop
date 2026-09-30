<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['kasir', 'owner']) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_available' => ['nullable', 'boolean'],
            // Recipes BOM (Bill of Materials)
            'recipes' => ['required', 'array', 'min:1'],
            'recipes.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'recipes.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori menu wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'name.required' => 'Nama menu wajib diisi.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'image.image' => 'Berkas gambar harus berupa gambar (jpg, png, webp).',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'recipes.required' => 'Komposisi bahan baku (resep) wajib ditentukan minimal 1 bahan.',
            'recipes.*.ingredient_id.required' => 'Bahan baku wajib dipilih.',
            'recipes.*.amount.required' => 'Takaran bahan baku (gram/ml/pcs) wajib diisi.',
            'recipes.*.amount.min' => 'Takaran bahan minimal 0.01.',
        ];
    }
}
