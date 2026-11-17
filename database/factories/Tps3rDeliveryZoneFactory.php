<?php

namespace Database\Factories;

use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tps3rDeliveryZone>
 */
class Tps3rDeliveryZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengelola_tps3r_profile_id' => PengelolaTps3rProfile::factory(),
            'kelurahan' => fake()->randomElement(['Klegen', 'Pandean', 'Oro-Oro Ombo', 'Nambangan Lor']),
            'kecamatan' => fake()->randomElement(['Kartoharjo', 'Taman', 'Manguharjo']),
            'delivery_fee' => fake()->randomElement([0, 5000, 7000, 10000]),
            'status' => Tps3rDeliveryZone::STATUS_PENDING,
            'is_active' => true,
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Tps3rDeliveryZone::STATUS_APPROVED,
            'approved_by' => User::factory()->adminDlh(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Tps3rDeliveryZone::STATUS_REJECTED,
            'approved_by' => User::factory()->adminDlh(),
            'approved_at' => null,
            'rejection_reason' => 'Wilayah belum didukung armada aktif.',
        ]);
    }
}
