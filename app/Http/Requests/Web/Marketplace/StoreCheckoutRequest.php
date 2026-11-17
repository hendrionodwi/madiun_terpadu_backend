<?php

namespace App\Http\Requests\Web\Marketplace;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isWarga() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'metode_pengiriman' => ['required', 'string', 'in:diantar,ambil_mandiri'],
            'alamat_pengiriman' => ['nullable', 'required_if:metode_pengiriman,diantar', 'string', 'max:500'],
            'shipping_source' => ['nullable', 'required_if:metode_pengiriman,diantar', 'string', 'in:local_tps3r,rajaongkir'],
            'destination_kelurahan' => ['exclude_unless:shipping_source,local_tps3r', 'required', 'string', 'max:100'],
            'destination_kecamatan' => ['exclude_unless:shipping_source,local_tps3r', 'required', 'string', 'in:Kartoharjo,Manguharjo,Taman'],
            'rajaongkir_destination_id' => ['exclude_unless:shipping_source,rajaongkir', 'required', 'integer', 'min:1'],
            'shipping_courier' => ['exclude_unless:shipping_source,rajaongkir', 'required', 'string', 'max:50', 'regex:/^[a-z0-9_:-]+$/'],
            'shipping_service' => ['exclude_unless:shipping_source,rajaongkir', 'required', 'string', 'max:100'],
            'metode_pembayaran' => ['required', 'string', 'in:poin_sampah,midtrans'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'alamat_pengiriman.required_if' => 'Alamat pengiriman wajib diisi untuk layanan antar.',
            'metode_pembayaran.in' => 'Pilih pembayaran menggunakan poin atau Midtrans.',
            'shipping_source.required_if' => 'Pilih layanan pengiriman armada TPS3R atau kurir luar kota.',
            'destination_kelurahan.required' => 'Pilih kelurahan tujuan pengiriman.',
            'destination_kecamatan.required' => 'Pilih kecamatan tujuan pengiriman.',
            'rajaongkir_destination_id.required' => 'Pilih tujuan pengiriman luar kota.',
            'shipping_courier.required' => 'Pilih kurir pengiriman.',
            'shipping_service.required' => 'Pilih layanan kurir.',
        ];
    }
}
