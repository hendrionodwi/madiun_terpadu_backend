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

    /**
     * Indicate that the TPS3R has active Midtrans credentials.
     */
    public function withMidtrans(bool $isProduction = false): static
    {
        return $this->state(fn (array $attributes) => [
            'midtrans_merchant_id' => 'M-'.fake()->numerify('######'),
            'midtrans_client_key' => 'SB-Mid-client-'.fake()->lexify('????????????'),
            'midtrans_server_key' => 'SB-Mid-server-'.fake()->lexify('????????????'),
            'midtrans_is_production' => $isProduction,
            'midtrans_is_active' => true,
        ]);
    }
}
