<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FnbMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ SECURITY FIX: Proper authorization check for FnB menu management
        // Only authenticated users with pengguna_properti or owner role can manage menu items
        return auth()->check() &&
               (auth()->user()->hasRole('pengguna_properti') || auth()->user()->hasRole('owner'));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|in:breakfast,lunch,dinner,beverage,snack,dessert',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_available' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi',
            'category.required' => 'Kategori menu wajib dipilih',
            'category.in' => 'Kategori tidak valid',
            'price.required' => 'Harga wajib diisi',
            'price.numeric' => 'Harga harus berupa angka',
            'price.min' => 'Harga tidak boleh negatif',
        ];
    }
}
