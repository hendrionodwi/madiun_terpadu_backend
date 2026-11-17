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

    /**
     * @param  list<array{product_id: int, jumlah: int}>  $items
     * @return array{
     *     items: list<array{product_id: int, jumlah: int}>,
     *     metode_pembayaran: string,
     *     metode_pengiriman: string,
     *     alamat_pengiriman: string|null,
     *     shipping_source: string|null,
     *     destination_kelurahan: string|null,
     *     destination_kecamatan: string|null,
     *     rajaongkir_destination_id: int|null,
     *     shipping_courier: string|null,
     *     shipping_service: string|null,
     *     catatan: string|null
     * }
     */
    public function orderData(array $items): array
    {
        $validated = $this->validated();

        return [
            'items' => $items,
            'metode_pembayaran' => (string) $validated['metode_pembayaran'],
            'metode_pengiriman' => (string) $validated['metode_pengiriman'],
            'alamat_pengiriman' => isset($validated['alamat_pengiriman'])
                ? (string) $validated['alamat_pengiriman']
                : null,
            'shipping_source' => isset($validated['shipping_source'])
                ? (string) $validated['shipping_source']
                : null,
            'destination_kelurahan' => isset($validated['destination_kelurahan'])
                ? (string) $validated['destination_kelurahan']
                : null,
            'destination_kecamatan' => isset($validated['destination_kecamatan'])
                ? (string) $validated['destination_kecamatan']
                : null,
            'rajaongkir_destination_id' => isset($validated['rajaongkir_destination_id'])
                ? (int) $validated['rajaongkir_destination_id']
                : null,
            'shipping_courier' => isset($validated['shipping_courier'])
                ? (string) $validated['shipping_courier']
                : null,
            'shipping_service' => isset($validated['shipping_service'])
                ? (string) $validated['shipping_service']
                : null,
            'catatan' => isset($validated['catatan']) ? (string) $validated['catatan'] : null,
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
