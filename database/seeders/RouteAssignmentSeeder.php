<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\RouteAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class RouteAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (RouteAssignment::count() > 0) {
            return;
        }

        $petugas = User::where('role', UserRole::PetugasLapangan)->first();
        $admin = User::where('role', UserRole::AdminDlh)->first();

        if (! $petugas || ! $admin) {
            return;
        }

        RouteAssignment::firstOrCreate(
            ['route_code' => 'R3'],
            [
                'petugas_id' => $petugas->id,
                'assigned_by' => $admin->id,
                'route_name' => 'Rute Kartoharjo Pusat - Oro-Oro Ombo (R3)',
                'district' => 'Kartoharjo',
                'subdistrict' => 'Oro-Oro Ombo',
                'start_location' => 'Depo Truk Kartoharjo Dr3',
                'end_facility' => 'TPS3R Bersih Berseri Kartoharjo Tr3',
                'schedule_day' => 'Setiap Hari',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'is_active' => true,
                'latitude' => -7.6180,
                'longitude' => 111.5300,
                'waypoints' => [
                    [-7.6180, 111.5300],
                    [-7.6210, 111.5280],
                    [-7.6240, 111.5260],
                    [-7.6270, 111.5240],
                    [-7.6298, 111.5239],
                ],
                'notes' => 'Armada dump truck pengangkutan harian wilayah Kartoharjo',
            ]
        );
    }
}
