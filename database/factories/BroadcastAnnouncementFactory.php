<?php

namespace Database\Factories;

use App\Models\BroadcastAnnouncement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BroadcastAnnouncement>
 */
class BroadcastAnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->adminDlh(),
            'title' => fake()->sentence(4),
            'summary' => fake()->sentence(10),
            'content' => fake()->paragraph(),
            'audience_role' => null,
            'priority' => 'normal',
            'status' => 'published',
            'kategori' => 'informasi',
            'is_active' => true,
            'show_on_portal' => true,
            'published_at' => now(),
            'expires_at' => now()->addMonth(),
        ];
    }
}
