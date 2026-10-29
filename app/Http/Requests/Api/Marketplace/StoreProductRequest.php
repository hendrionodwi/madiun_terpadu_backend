<?php

namespace App\Http\Requests\Api\Marketplace;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_produk' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'kategori' => ['required', 'string', 'in:pupuk_kompos,kerajinan_plastik,maggot_pakan,ecobrick,lainnya'],
            'harga' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'stok' => ['required', 'integer', 'min:0'],
            'foto' => $this->hasFile('foto')
                ? ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048']
                : ['nullable', 'string', 'max:255'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'kategori.required' => 'Kategori produk daur ulang wajib dipilih.',
            'kategori.in' => 'Kategori harus salah satu dari: pupuk_kompos, kerajinan_plastik, maggot_pakan, ecobrick, lainnya.',
            'harga.required' => 'Harga produk wajib diisi.',
            'stok.required' => 'Stok produk wajib diisi.',
            'foto.image' => 'File foto produk harus berupa gambar.',
            'foto.mimes' => 'Format foto harus berupa jpeg, png, jpg, atau webp.',
            'foto.max' => 'Ukuran foto maksimal adalah 2MB.',
        ];
    }
}
