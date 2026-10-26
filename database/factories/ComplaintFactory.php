<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_tiket' => 'TKT-'.date('Ymd').'-'.strtoupper(Str::random(6)),
            'pelapor_id' => User::factory()->warga(),
            'judul' => fake()->sentence(4),
            'kategori_masalah' => fake()->randomElement(['sampah_liar', 'tps_meluap', 'saluran_tersumbat', 'pembakaran_sampah', 'pencemaran_lingkungan']),
            'deskripsi' => fake()->paragraph(),
            'lokasi' => fake()->address(),
            'kelurahan' => fake()->randomElement(['Pandean', 'Kartanegara', 'Kanigoro', 'Mojorejo']),
            'kecamatan' => fake()->randomElement(['Taman', 'Kartoharjo', 'Manguharjo']),
            'latitude' => fake()->latitude(-7.65, -7.61),
            'longitude' => fake()->longitude(111.51, 111.55),
            'foto' => null,
            'is_anonim' => false,
            'status' => 'diterima',
        ];
    }
}
