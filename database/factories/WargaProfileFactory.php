<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WargaProfile>
 */
class WargaProfileFactory extends Factory
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
            'nik' => fake()->unique()->numerify('3577############'),
            'alamat' => fake()->streetAddress(),
            'rt' => str_pad((string) fake()->numberBetween(1, 15), 3, '0', STR_PAD_LEFT),
            'rw' => str_pad((string) fake()->numberBetween(1, 10), 3, '0', STR_PAD_LEFT),
            'kelurahan' => fake()->randomElement(['Pandean', 'Kartanegara', 'Kanigoro', 'Mojorejo', 'Nambangan Lor']),
            'kecamatan' => fake()->randomElement(['Taman', 'Kartoharjo', 'Manguharjo']),
            'latitude' => fake()->latitude(-7.65, -7.61),
            'longitude' => fake()->longitude(111.51, 111.55),
            'saldo_poin' => fake()->numberBetween(0, 5000),
        ];
    }
}
