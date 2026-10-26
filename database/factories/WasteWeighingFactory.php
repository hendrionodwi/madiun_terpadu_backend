<?php

namespace Database\Factories;

use App\Models\PickupSchedule;
use App\Models\User;
use App\Models\WasteWeighing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WasteWeighing>
 */
class WasteWeighingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $beratKg = fake()->randomFloat(2, 1, 20);
        $poinPerKg = 100;

        return [
            'warga_id' => User::factory()->warga(),
            'petugas_id' => User::factory()->petugasLapangan(),
            'pickup_schedule_id' => PickupSchedule::factory(),
            'jenis_sampah' => fake()->randomElement(['organik', 'anorganik_plastik', 'anorganik_kertas', 'anorganik_logam']),
            'berat_kg' => $beratKg,
            'poin_per_kg' => $poinPerKg,
            'poin_didapat' => (int) round($beratKg * $poinPerKg),
            'catatan' => 'Hasil timbangan sampah pilah',
        ];
    }
}
