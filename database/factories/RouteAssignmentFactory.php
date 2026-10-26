<?php

namespace Database\Factories;

use App\Models\RouteAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouteAssignment>
 */
class RouteAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'petugas_id' => User::factory()->petugasLapangan(),
            'assigned_by' => User::factory()->adminDlh(),
            'route_code' => 'RUTE-'.fake()->unique()->numerify('###'),
            'route_name' => 'Rute '.fake()->streetName(),
            'district' => fake()->randomElement(['Taman', 'Kartoharjo', 'Manguharjo']),
            'subdistrict' => fake()->citySuffix(),
            'schedule_day' => fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']),
            'start_time' => '07:00',
            'end_time' => '12:00',
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
