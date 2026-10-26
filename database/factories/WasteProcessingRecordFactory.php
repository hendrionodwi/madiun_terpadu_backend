<?php

namespace Database\Factories;

use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use App\Models\WasteProcessingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WasteProcessingRecord>
 */
class WasteProcessingRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pengelola = User::factory()->pengelolaTps3r();

        return [
            'pengelola_id' => $pengelola,
            'tps3r_profile_id' => PengelolaTps3rProfile::factory()->for($pengelola, 'user'),
            'tps3r_waste_deposit_id' => null,
            'process_method' => fake()->randomElement(['composting', 'sorting', 'shredding']),
            'waste_type' => fake()->randomElement(['organik', 'anorganik_daur_ulang', 'residu']),
            'input_weight_kg' => fake()->randomFloat(2, 10, 500),
            'output_weight_kg' => null,
            'status' => 'planned',
            'started_on' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'completed_on' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
