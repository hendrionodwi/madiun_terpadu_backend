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
        return $this->user()?->isWarga() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'metode_pengiriman' => $this->input('metode_pengiriman', 'ambil_mandiri'),
        ]);
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
            'metode_pengiriman' => ['required', 'string', 'in:diantar,ambil_mandiri'],
            'alamat_pengiriman' => ['nullable', 'required_if:metode_pengiriman,diantar', 'string', 'max:500'],
            'shipping_source' => ['nullable', 'required_if:metode_pengiriman,diantar', 'string', 'in:local_tps3r,rajaongkir'],
            'destination_kelurahan' => ['exclude_unless:shipping_source,local_tps3r', 'required', 'string', 'max:100'],
            'destination_kecamatan' => ['exclude_unless:shipping_source,local_tps3r', 'required', 'string', 'in:Kartoharjo,Manguharjo,Taman'],
            'rajaongkir_destination_id' => ['exclude_unless:shipping_source,rajaongkir', 'required', 'integer', 'min:1'],
            'shipping_courier' => ['exclude_unless:shipping_source,rajaongkir', 'required', 'string', 'max:50', 'regex:/^[a-z0-9_:-]+$/'],
            'shipping_service' => ['exclude_unless:shipping_source,rajaongkir', 'required', 'string', 'max:100'],
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
            'metode_pengiriman.in' => 'Metode pengiriman harus diantar atau ambil mandiri.',
            'shipping_source.required_if' => 'Pilih layanan pengiriman armada TPS3R atau kurir luar kota.',
            'destination_kelurahan.required' => 'Kelurahan tujuan wajib dipilih.',
            'destination_kecamatan.required' => 'Kecamatan tujuan wajib dipilih.',
            'rajaongkir_destination_id.required' => 'Tujuan kurir luar kota wajib dipilih.',
            'shipping_courier.required' => 'Kurir pengiriman wajib dipilih.',
            'shipping_service.required' => 'Layanan kurir wajib dipilih.',
        ];
    }
}
