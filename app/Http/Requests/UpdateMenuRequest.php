<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
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
            'recipes' => ['nullable', 'array'],
            'recipes.*.ingredient_id' => ['required_with:recipes', 'exists:ingredients,id'],
            'recipes.*.amount' => ['required_with:recipes', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori menu wajib dipilih.',
            'name.required' => 'Nama menu wajib diisi.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'image.image' => 'Berkas gambar harus berupa gambar valid.',
        ];
    }
}
