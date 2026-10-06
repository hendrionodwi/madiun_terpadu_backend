<?php

namespace Database\Factories;

use App\Models\PetugasLapanganProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetugasLapanganProfile>
 */
class PetugasLapanganProfileFactory extends Factory
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
            'nip' => fake()->numerify('1990##########'),
            'id_wilayah_rute' => 'RUTE-'.fake()->numerify('###'),
            'nama_wilayah_tugas' => fake()->randomElement(['Wilayah Manguharjo Utara', 'Wilayah Kartoharjo Barat', 'Wilayah Taman Tengah']),
            'jenis_kendaraan' => fake()->randomElement(['Gerobak Motor', 'Truk Dump', 'Armroll Truk']),
            'plat_nomor' => 'AE '.fake()->numberBetween(1000, 9999).' '.fake()->lexify('??'),
            'status_tugas' => fake()->randomElement(['standby', 'bertugas', 'selesai']),
        ];
    }
}
