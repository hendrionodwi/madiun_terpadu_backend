<?php

namespace Database\Factories;

use App\Models\EnvironmentalReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnvironmentalReport>
 */
class EnvironmentalReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pemrakarsa_id' => User::factory()->pemrakarsa(),
            'judul_laporan' => 'Laporan Pemantauan Lingkungan '.fake()->company(),
            'periode_semester' => fake()->randomElement(['Semester 1 (Jan - Jun)', 'Semester 2 (Jul - Des)']),
            'tahun' => 2026,
            'jenis_dokumen' => fake()->randomElement(['SPPL', 'UKL-UPL', 'AMDAL']),
            'timbulan_sampah_kg' => fake()->randomFloat(2, 500, 5000),
            'metode_pengelolaan' => 'Pemilahan mandiri 3 wadah dan kerjasama pembuangan dengan TPS3R',
            'file_path' => 'reports/laporan_semester_dummy.pdf',
            'status' => 'menunggu_verifikasi',
            'catatan_verifikasi' => null,
            'verified_at' => null,
        ];
    }
}
