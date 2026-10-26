<?php

namespace Database\Factories;

use App\Models\PickupSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PickupSchedule>
 */
class PickupScheduleFactory extends Factory
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
            'id_wilayah_rute' => 'RUTE-KART-01',
            'nama_wilayah_rute' => 'Rute Kartoharjo Pusat',
            'tanggal_penjemputan' => fake()->date(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '11:00',
            'status' => 'terjadwal',
            'keterangan' => 'Penjemputan rutin sampah pilah',
        ];
    }
}
