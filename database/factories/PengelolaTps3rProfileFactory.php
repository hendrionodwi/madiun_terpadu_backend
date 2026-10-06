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
            'alamat' => fake()->streetAddress(),
            'kelurahan' => fake()->randomElement(['Pandean', 'Oro-Oro Ombo', 'Demangan', 'Klegen']),
            'kecamatan' => fake()->randomElement(['Taman', 'Kartoharjo', 'Manguharjo']),
            'kapasitas_harian_kg' => fake()->randomFloat(2, 500, 3000),
            'latitude' => fake()->latitude(-7.65, -7.61),
            'longitude' => fake()->longitude(111.51, 111.55),
        ];
    }
}
