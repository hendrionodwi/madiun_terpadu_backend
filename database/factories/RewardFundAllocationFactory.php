<?php

namespace Database\Factories;

use App\Models\RewardFundAllocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RewardFundAllocation>
 */
class RewardFundAllocationFactory extends Factory
{
    protected $model = RewardFundAllocation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'source_type' => fake()->randomElement(['apbd', 'penjualan_daur_ulang', 'csr_kemitraan', 'retribusi']),
            'amount' => fake()->randomElement([5000000, 10000000, 25000000, 50000000]),
            'fiscal_year' => 2026,
            'allocated_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'reference_number' => 'DPA-DLH/2026/'.fake()->numerify('###'),
            'notes' => fake()->sentence(),
        ];
    }
}
