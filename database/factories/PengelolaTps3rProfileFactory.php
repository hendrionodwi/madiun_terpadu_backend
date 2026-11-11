<?php

namespace Database\Factories;

use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PengelolaTps3rProfile>
 */
class PengelolaTps3rProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama_tps3r' => 'TPS3R '.fake()->company(),
            'kode_tps3r' => 'TPS3R-'.fake()->unique()->numerify('###'),
            'jenis_fasilitas' => 'tps3r',
            'alamat' => fake()->streetAddress(),
            'kelurahan' => fake()->randomElement(['Pandean', 'Oro-Oro Ombo', 'Demangan', 'Klegen']),
            'kecamatan' => fake()->randomElement(['Taman', 'Kartoharjo', 'Manguharjo']),
            'jam_operasional' => '07.00 - 15.30 WIB (Senin - Sabtu)',
            'layanan_tersedia' => ['Penerimaan Sampah Pilah Mandiri', 'Pengomposan Skala Komunal'],
            'kategori_sampah_diterima' => ['Organik', 'Plastik Botol PET', 'Kardus & Kertas'],
            'fokus_pengolahan' => 'Kompos Aerobik & Pemilahan Anorganik',
            'cakupan_wilayah' => 'Kelurahan Sekitar Kota Madiun',
            'nama_koordinator' => fake()->name(),
            'kapasitas_harian_kg' => fake()->randomFloat(2, 500, 3000),
            'latitude' => fake()->latitude(-7.65, -7.61),
            'longitude' => fake()->longitude(111.51, 111.55),
        ];
    }

    /**
     * Indicate that the TPS3R has active Midtrans credentials.
     */
    public function withMidtrans(bool $isProduction = false): static
    {
        return $this->state(fn (array $attributes) => [
            'midtrans_merchant_id' => 'M-'.fake()->numerify('######'),
            'midtrans_client_key' => 'SB-Mid-client-'.fake()->lexify('????????????'),
            'midtrans_server_key' => 'SB-Mid-server-'.fake()->lexify('????????????'),
            'midtrans_signature_secret' => 'sandbox-signature-secret-'.fake()->lexify('????????????????????????????????'),
            'midtrans_is_production' => $isProduction,
            'midtrans_is_active' => true,
        ]);
    }
}
