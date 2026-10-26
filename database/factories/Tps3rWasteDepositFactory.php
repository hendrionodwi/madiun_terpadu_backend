<?php

namespace Database\Factories;

use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tps3rWasteDeposit>
 */
class Tps3rWasteDepositFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengelola_id' => User::factory()->pengelolaTps3r(),
            'tps3r_profile_id' => null,
            'petugas_id' => User::factory()->petugasLapangan(),
            'nomor_resi' => 'DEP-'.date('Ymd').'-'.strtoupper(Str::random(6)),
            'sumber_sampah' => fake()->randomElement(['armada_kelurahan', 'warga_mandiri', 'pemrakarsa_komersial']),
            'jenis_sampah' => fake()->randomElement(['organik', 'anorganik_daur_ulang', 'residu']),
            'berat_kg' => fake()->randomFloat(2, 50, 500),
            'tanggal_setor' => fake()->date(),
            'catatan' => 'Setoran sampah terpilah dari armada',
        ];
    }
}
