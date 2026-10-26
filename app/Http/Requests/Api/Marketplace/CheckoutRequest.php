<?php

namespace App\Http\Requests\Api\Marketplace;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:waste_products,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'metode_pembayaran' => ['required', 'string', 'in:transfer_bank,poin_sampah,cod,midtrans'],
            'alamat_pengiriman' => ['required', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:500'],
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
            'items.required' => 'Keranjang belanja tidak boleh kosong.',
            'items.min' => 'Keranjang belanja minimal berisi 1 produk.',
            'items.*.product_id.required' => 'ID Produk dalam keranjang wajib ada.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak ditemukan dalam katalog.',
            'items.*.jumlah.min' => 'Jumlah pembelian minimal 1 item.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
            'metode_pembayaran.in' => 'Metode pembayaran harus salah satu dari: transfer_bank, poin_sampah, cod, midtrans.',
            'alamat_pengiriman.required' => 'Alamat pengiriman wajib diisi.',
        ];
    }
}
