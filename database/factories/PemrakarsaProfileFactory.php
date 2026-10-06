<?php

namespace Database\Factories;

use App\Models\PemrakarsaProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PemrakarsaProfile>
 */
class PemrakarsaProfileFactory extends Factory
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
            'nama_instansi' => fake()->company(),
            'jenis_instansi' => fake()->randomElement(['Hotel', 'Restoran', 'Rumah Sakit', 'Pusat Perbelanjaan', 'Industri Kreatif']),
            'nib' => fake()->numerify('#############'),
            'penanggung_jawab' => fake()->name(),
            'alamat_instansi' => fake()->address(),
            'latitude' => fake()->latitude(-7.65, -7.61),
            'longitude' => fake()->longitude(111.51, 111.55),
        ];
    }
}
