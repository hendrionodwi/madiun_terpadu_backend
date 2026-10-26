<?php

namespace Database\Factories;

use App\Models\EntityVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntityVerification>
 */
class EntityVerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->pengelolaTps3r(),
            'entity_type' => 'tps3r',
            'document_number' => fake()->numerify('NIB#############'),
            'document_path' => 'entity-documents/example.pdf',
            'status' => 'submitted',
            'admin_note' => null,
            'reviewed_by' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
        ];
    }
}
