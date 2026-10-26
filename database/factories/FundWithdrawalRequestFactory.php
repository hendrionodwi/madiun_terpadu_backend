<?php

namespace Database\Factories;

use App\Models\FundWithdrawalRequest;
use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundWithdrawalRequest>
 */
class FundWithdrawalRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->warga()->has(WargaProfile::factory()),
            'source' => 'points',
            'points_used' => 1000,
            'amount' => 10000,
            'destination_type' => 'e_wallet',
            'destination_account' => fake()->numerify('08##########'),
            'status' => 'pending',
            'requester_note' => fake()->optional()->sentence(),
            'admin_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
