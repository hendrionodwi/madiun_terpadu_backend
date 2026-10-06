<?php

namespace Database\Factories;

use App\Models\AdminDlhProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminDlhProfile>
 */
class AdminDlhProfileFactory extends Factory
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
            'nip' => fake()->numerify('1985##########'),
            'jabatan' => fake()->randomElement(['Kepala Bidang PSLB3', 'Pengawas Lingkungan Hidup', 'Staff Teknis Persampahan']),
            'bidang' => 'Bidang Pengelolaan Sampah dan Limbah B3',
        ];
    }
}
