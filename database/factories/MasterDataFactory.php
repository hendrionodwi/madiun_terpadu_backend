<?php

namespace Database\Factories;

use App\Models\MasterData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterData>
 */
class MasterDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => 'waste_category',
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'numeric_value' => fake()->numberBetween(50, 500),
            'unit' => 'poin/kg',
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
