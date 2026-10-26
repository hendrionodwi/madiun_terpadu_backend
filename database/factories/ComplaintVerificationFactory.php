<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintVerification>
 */
class ComplaintVerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'petugas_id' => User::factory()->petugasLapangan(),
            'status_verifikasi' => 'valid',
            'catatan_petugas' => 'Petugas telah meninjau lokasi dan sampah sedang dalam penanganan.',
            'foto_bukti' => null,
            'tanggal_verifikasi' => now(),
        ];
    }
}
