<?php

namespace App\Services;

use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use App\Models\WasteProduct;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type ShippingQuote array{
 *     source: string,
 *     fee: float,
 *     seller_id: int,
 *     destination_kelurahan: string|null,
 *     destination_kecamatan: string|null,
 *     rajaongkir_destination_id: int|null,
 *     courier: string|null,
 *     service: string|null,
 *     etd: string|null,
 *     weight_grams: int
 * }
 */
final class MarketplaceShippingService
{
    public function __construct(public RajaOngkirService $rajaOngkirService) {}

    /**
     * @param  Collection<int, WasteProduct>  $products
     * @param  array<int, int>  $quantitiesByProduct
     * @param  array<string, mixed>  $data
     * @return ShippingQuote
     */
    public function resolve(Collection $products, array $quantitiesByProduct, array $data): array
    {
        $sellerIds = $products->pluck('seller_id')->unique()->values();
        if ($sellerIds->count() !== 1) {
            throw ValidationException::withMessages([
                'items' => 'Produk dalam satu transaksi harus berasal dari fasilitas TPS3R yang sama.',
            ]);
        }

        $sellerId = (int) $sellerIds->first();
        $weightGrams = $this->calculateWeight($products, $quantitiesByProduct);
        $shippingMethod = (string) ($data['metode_pengiriman'] ?? 'ambil_mandiri');

        if ($shippingMethod === 'ambil_mandiri') {
            return $this->result('pickup', 0, $sellerId, $weightGrams);
        }

        $source = (string) ($data['shipping_source'] ?? '');
        if ($source === 'local_tps3r') {
            return $this->resolveLocalDelivery($sellerId, $weightGrams, $data);
        }

        if ($source === 'rajaongkir') {
            if (($data['metode_pembayaran'] ?? null) === 'poin_sampah') {
                throw ValidationException::withMessages([
                    'metode_pembayaran' => 'Poin hanya dapat digunakan untuk ambil mandiri atau pengiriman armada TPS3R di Kota Madiun.',
                ]);
            }

            return $this->resolveRajaOngkirDelivery($sellerId, $weightGrams, $data);
        }

        throw ValidationException::withMessages([
            'shipping_source' => 'Pilih layanan pengiriman armada TPS3R atau kurir luar kota.',
        ]);
    }

    /**
     * @param  Collection<int, WasteProduct>  $products
     * @param  array<int, int>  $quantitiesByProduct
     */
    public function calculateWeight(Collection $products, array $quantitiesByProduct): int
    {
        $productsById = $products->keyBy('id');
        $weightGrams = 0;

        foreach ($quantitiesByProduct as $productId => $quantity) {
            /** @var WasteProduct|null $product */
            $product = $productsById->get($productId);
            if (! $product) {
                throw ValidationException::withMessages(['items' => 'Salah satu produk tidak ditemukan.']);
            }

            $weightGrams += max(1, $product->weight_grams) * $quantity;
        }

        if ($weightGrams > 30000) {
            throw ValidationException::withMessages([
                'items' => 'Berat pesanan maksimal 30 kg untuk satu pengiriman. Kurangi jumlah produk.',
            ]);
        }

        return $weightGrams;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return ShippingQuote
     */
    private function resolveLocalDelivery(int $sellerId, int $weightGrams, array $data): array
    {
        $kelurahan = trim((string) ($data['destination_kelurahan'] ?? ''));
        $kecamatan = trim((string) ($data['destination_kecamatan'] ?? ''));
        $profile = $this->profileForSeller($sellerId);

        $zone = Tps3rDeliveryZone::query()
            ->approved()
            ->where('pengelola_tps3r_profile_id', $profile->id)
            ->whereRaw('LOWER(kelurahan) = ?', [mb_strtolower($kelurahan)])
            ->whereRaw('LOWER(kecamatan) = ?', [mb_strtolower($kecamatan)])
            ->first();

        if (! $zone) {
            throw ValidationException::withMessages([
                'destination_kelurahan' => 'Alamat belum termasuk wilayah pengiriman armada TPS3R ini.',
            ]);
        }

        return $this->result(
            'local_tps3r',
            $zone->delivery_fee,
            $sellerId,
            $weightGrams,
            destinationKelurahan: $zone->kelurahan,
            destinationKecamatan: $zone->kecamatan,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return ShippingQuote
     */
    private function resolveRajaOngkirDelivery(int $sellerId, int $weightGrams, array $data): array
    {
        $profile = $this->profileForSeller($sellerId);
        if (! $profile->rajaongkir_origin_id) {
            throw ValidationException::withMessages([
                'shipping_source' => 'TPS3R belum mengatur lokasi asal untuk pengiriman luar kota.',
            ]);
        }

        $destinationId = (int) ($data['rajaongkir_destination_id'] ?? 0);
        $courier = mb_strtolower(trim((string) ($data['shipping_courier'] ?? '')));
        $service = trim((string) ($data['shipping_service'] ?? ''));
        if (! $this->rajaOngkirService->isCourierAllowed($courier)) {
            throw ValidationException::withMessages([
                'shipping_courier' => 'Kurir tersebut tidak diaktifkan oleh pengelola sistem.',
            ]);
        }
        $quotes = $this->rajaOngkirService->calculateDomesticCosts(
            $profile->rajaongkir_origin_id,
            $destinationId,
            $weightGrams,
            $courier,
        );

        $selectedQuote = collect($quotes)->first(
            static fn (array $quote): bool => $quote['courier_code'] === $courier
                && mb_strtolower($quote['service']) === mb_strtolower($service),
        );

        if (! $selectedQuote) {
            throw ValidationException::withMessages([
                'shipping_service' => 'Layanan kurir sudah berubah. Hitung ulang ongkir sebelum membuat pesanan.',
            ]);
        }

        return $this->result(
            'rajaongkir',
            (float) $selectedQuote['cost'],
            $sellerId,
            $weightGrams,
            rajaOngkirDestinationId: $destinationId,
            courier: $selectedQuote['courier_code'],
            service: $selectedQuote['service'],
            etd: $selectedQuote['etd'],
        );
    }

    private function profileForSeller(int $sellerId): PengelolaTps3rProfile
    {
        $profile = User::query()->with('pengelolaTps3rProfile')->find($sellerId)?->pengelolaTps3rProfile;
        if (! $profile) {
            throw ValidationException::withMessages([
                'items' => 'Profil pengiriman TPS3R tidak tersedia.',
            ]);
        }

        return $profile;
    }

    /**
     * @return ShippingQuote
     */
    private function result(
        string $source,
        float $fee,
        int $sellerId,
        int $weightGrams,
        ?string $destinationKelurahan = null,
        ?string $destinationKecamatan = null,
        ?int $rajaOngkirDestinationId = null,
        ?string $courier = null,
        ?string $service = null,
        ?string $etd = null,
    ): array {
        return [
            'source' => $source,
            'fee' => $fee,
            'seller_id' => $sellerId,
            'destination_kelurahan' => $destinationKelurahan,
            'destination_kecamatan' => $destinationKecamatan,
            'rajaongkir_destination_id' => $rajaOngkirDestinationId,
            'courier' => $courier,
            'service' => $service,
            'etd' => $etd,
            'weight_grams' => $weightGrams,
        ];
    }
}
