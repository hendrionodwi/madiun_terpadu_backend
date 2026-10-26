<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\PickupSchedule;
use App\Models\RouteAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class PickupScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $petugasList = User::where('role', UserRole::PetugasLapangan)->get();
        $wargaList = User::where('role', UserRole::Warga)->get();

        if ($petugasList->isEmpty() || $wargaList->isEmpty()) {
            return;
        }

        $wargaA = $wargaList->first();
        $wargaB = $wargaList->skip(1)->first() ?? $wargaA;

        foreach ($petugasList as $petugas) {
            $route = RouteAssignment::where('petugas_id', $petugas->id)->first()
                ?? RouteAssignment::first();

            $routeCode = $route?->route_code ?? 'RUTE-KART-01';
            $routeName = $route?->route_name ?? 'Rute Kartoharjo Pusat - Oro-Oro Ombo';

            $district = strtolower((string) ($route?->district ?? 'kartoharjo'));
            if (str_contains($district, 'taman')) {
                $mockPickups = [
                    [
                        'jenis_sampah' => 'anorganik_plastik',
                        'alamat_penjemputan' => 'Jl. Salak No. 12, Pandean, Taman',
                        'latitude' => -7.6350,
                        'longitude' => 111.5320,
                        'waktu_mulai' => '07:00',
                        'waktu_selesai' => '08:30',
                        'status' => 'terjadwal',
                        'keterangan' => '3 kantong plastik pilahan & botol PET',
                        'warga_id' => $wargaA->id,
                    ],
                    [
                        'jenis_sampah' => 'organik',
                        'alamat_penjemputan' => 'Jl. Serayu Baru No. 24, Pandean, Taman',
                        'latitude' => -7.6380,
                        'longitude' => 111.5300,
                        'waktu_mulai' => '08:30',
                        'waktu_selesai' => '09:45',
                        'status' => 'berlangsung',
                        'keterangan' => 'Sisa organik dapur rumah tangga siap kompos',
                        'warga_id' => $wargaB->id,
                    ],
                    [
                        'jenis_sampah' => 'anorganik_kertas',
                        'alamat_penjemputan' => 'Jl. Taman Praja No. 5, Mojorejo, Taman',
                        'latitude' => -7.6410,
                        'longitude' => 111.5280,
                        'waktu_mulai' => '10:00',
                        'waktu_selesai' => '11:15',
                        'status' => 'terjadwal',
                        'keterangan' => 'Kardus tebal dan tumpukan kertas arsip bekas',
                        'warga_id' => $wargaA->id,
                    ],
                    [
                        'jenis_sampah' => 'anorganik_logam',
                        'alamat_penjemputan' => 'Jl. Manisrejo No. 8, Manisrejo, Taman',
                        'latitude' => -7.6440,
                        'longitude' => 111.5260,
                        'waktu_mulai' => '11:15',
                        'waktu_selesai' => '12:30',
                        'status' => 'terjadwal',
                        'keterangan' => 'Kaleng bekas kemasan & logam ringan',
                        'warga_id' => $wargaB->id,
                    ],
                ];
            } elseif (str_contains($district, 'mangu')) {
                $mockPickups = [
                    [
                        'jenis_sampah' => 'anorganik_plastik',
                        'alamat_penjemputan' => 'Jl. Hayam Wuruk No. 15, Manguharjo',
                        'latitude' => -7.6275,
                        'longitude' => 111.5150,
                        'waktu_mulai' => '07:00',
                        'waktu_selesai' => '08:30',
                        'status' => 'terjadwal',
                        'keterangan' => '3 kantong plastik pilahan & botol PET',
                        'warga_id' => $wargaA->id,
                    ],
                    [
                        'jenis_sampah' => 'organik',
                        'alamat_penjemputan' => 'Jl. Gajah Mada No. 20, Winongo, Manguharjo',
                        'latitude' => -7.6250,
                        'longitude' => 111.5130,
                        'waktu_mulai' => '08:30',
                        'waktu_selesai' => '09:45',
                        'status' => 'berlangsung',
                        'keterangan' => 'Sisa organik dapur rumah tangga siap kompos',
                        'warga_id' => $wargaB->id,
                    ],
                    [
                        'jenis_sampah' => 'anorganik_kertas',
                        'alamat_penjemputan' => 'Jl. Ring Road Barat No. 8, Sogaten, Manguharjo',
                        'latitude' => -7.6220,
                        'longitude' => 111.5110,
                        'waktu_mulai' => '10:00',
                        'waktu_selesai' => '11:15',
                        'status' => 'terjadwal',
                        'keterangan' => 'Kardus tebal dan tumpukan kertas arsip bekas',
                        'warga_id' => $wargaA->id,
                    ],
                    [
                        'jenis_sampah' => 'anorganik_logam',
                        'alamat_penjemputan' => 'Jl. Pandan No. 12, Pangongangan, Manguharjo',
                        'latitude' => -7.6200,
                        'longitude' => 111.5180,
                        'waktu_mulai' => '11:15',
                        'waktu_selesai' => '12:30',
                        'status' => 'terjadwal',
                        'keterangan' => 'Kaleng bekas kemasan & logam ringan',
                        'warga_id' => $wargaB->id,
                    ],
                ];
            } else {
                $mockPickups = [
                    [
                        'jenis_sampah' => 'anorganik_plastik',
                        'alamat_penjemputan' => 'Jl. Diponegoro No. 18, Oro-Oro Ombo, Kartoharjo',
                        'latitude' => -7.6195,
                        'longitude' => 111.5285,
                        'waktu_mulai' => '07:00',
                        'waktu_selesai' => '08:30',
                        'status' => 'terjadwal',
                        'keterangan' => '3 kantong plastik pilahan & botol PET',
                        'warga_id' => $wargaA->id,
                    ],
                    [
                        'jenis_sampah' => 'organik',
                        'alamat_penjemputan' => 'Jl. Serayu No. 42, Kartoharjo',
                        'latitude' => -7.6225,
                        'longitude' => 111.5265,
                        'waktu_mulai' => '08:30',
                        'waktu_selesai' => '09:45',
                        'status' => 'berlangsung',
                        'keterangan' => 'Sisa organik dapur rumah tangga siap kompos',
                        'warga_id' => $wargaB->id,
                    ],
                    [
                        'jenis_sampah' => 'anorganik_kertas',
                        'alamat_penjemputan' => 'Jl. Musi No. 9, Kanigoro, Kartoharjo',
                        'latitude' => -7.6255,
                        'longitude' => 111.5245,
                        'waktu_mulai' => '10:00',
                        'waktu_selesai' => '11:15',
                        'status' => 'terjadwal',
                        'keterangan' => 'Kardus tebal dan tumpukan kertas arsip bekas',
                        'warga_id' => $wargaA->id,
                    ],
                    [
                        'jenis_sampah' => 'anorganik_logam',
                        'alamat_penjemputan' => 'Jl. Kelapa Manis No. 7, Rejomulyo, Kartoharjo',
                        'latitude' => -7.6280,
                        'longitude' => 111.5310,
                        'waktu_mulai' => '11:15',
                        'waktu_selesai' => '12:30',
                        'status' => 'terjadwal',
                        'keterangan' => 'Kaleng bekas kemasan & logam ringan',
                        'warga_id' => $wargaB->id,
                    ],
                ];
            }

            foreach ($mockPickups as $item) {
                PickupSchedule::firstOrCreate(
                    [
                        'petugas_id' => $petugas->id,
                        'alamat_penjemputan' => $item['alamat_penjemputan'],
                        'tanggal_penjemputan' => now()->toDateString(),
                    ],
                    array_merge($item, [
                        'id_wilayah_rute' => $routeCode,
                        'nama_wilayah_rute' => $routeName,
                        'tanggal_penjemputan' => now()->toDateString(),
                    ])
                );
            }
        }
    }
}
