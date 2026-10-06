<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AdminDlhProfile;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\PetugasLapanganProfile;
use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin DLH
        $admin = User::firstOrCreate(
            ['email' => 'admin.dlh@madiunkota.go.id'],
            [
                'name' => 'Administrator DLH Kota Madiun',
                'password' => Hash::make('password'),
                'role' => UserRole::AdminDlh,
                'phone_number' => '081234567890',
                'is_active' => true,
            ]
        );
        AdminDlhProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'nip' => '198501012010011001',
                'jabatan' => 'Kepala Bidang Pengelolaan Sampah dan Limbah B3',
                'bidang' => 'Bidang Pengelolaan Sampah dan Limbah B3',
            ]
        );

        // 2. Petugas Lapangan
        $petugas = User::firstOrCreate(
            ['email' => 'petugas.lapangan@madiunkota.go.id'],
            [
                'name' => 'Budi Santoso (Petugas Lapangan)',
                'password' => Hash::make('password'),
                'role' => UserRole::PetugasLapangan,
                'phone_number' => '081234567891',
                'is_active' => true,
            ]
        );
        PetugasLapanganProfile::firstOrCreate(
            ['user_id' => $petugas->id],
            [
                'nip' => '199203152018021002',
                'id_wilayah_rute' => 'RUTE-KART-01',
                'nama_wilayah_tugas' => 'Rute Kartoharjo Pusat - Oro-Oro Ombo',
                'jenis_kendaraan' => 'Dump Truck',
                'plat_nomor' => 'AE 8123 AA',
                'status_tugas' => 'standby',
            ]
        );

        // 3. Pengelola TPS3R
        $pengelola = User::firstOrCreate(
            ['email' => 'pengelola.tps3r@madiunkota.go.id'],
            [
                'name' => 'Ahmad Fauzi (TPS3R Kartoharjo)',
                'password' => Hash::make('password'),
                'role' => UserRole::PengelolaTps3r,
                'phone_number' => '081234567892',
                'is_active' => true,
            ]
        );
        PengelolaTps3rProfile::firstOrCreate(
            ['user_id' => $pengelola->id],
            [
                'nama_tps3r' => 'TPS3R Bersih Berseri Kartoharjo',
                'kode_tps3r' => 'TPS3R-KTH-01',
                'alamat' => 'Jl. Serayu No. 45, Kartoharjo',
                'kelurahan' => 'Kartanegara',
                'kecamatan' => 'Kartoharjo',
                'kapasitas_harian_kg' => 1500.00,
                'latitude' => -7.6325000,
                'longitude' => 111.5312000,
            ]
        );

        // 4. Pemrakarsa
        $pemrakarsa = User::firstOrCreate(
            ['email' => 'pemrakarsa@madiunkota.go.id'],
            [
                'name' => 'PT Madiun Indah Mall (Pemrakarsa)',
                'password' => Hash::make('password'),
                'role' => UserRole::Pemrakarsa,
                'phone_number' => '081234567893',
                'is_active' => true,
            ]
        );
        PemrakarsaProfile::firstOrCreate(
            ['user_id' => $pemrakarsa->id],
            [
                'nama_instansi' => 'PT Madiun Indah Plaza',
                'jenis_instansi' => 'Pusat Perbelanjaan / Mall',
                'nib' => '9120001234567',
                'penanggung_jawab' => 'Hendro Wijaya',
                'alamat_instansi' => 'Jl. Pahlawan No. 10, Kota Madiun',
                'latitude' => -7.6289000,
                'longitude' => 111.5245000,
            ]
        );

        // 5. Warga
        $warga = User::firstOrCreate(
            ['email' => 'warga@madiunkota.go.id'],
            [
                'name' => 'Siti Aminah (Warga)',
                'password' => Hash::make('password'),
                'role' => UserRole::Warga,
                'phone_number' => '081234567894',
                'is_active' => true,
            ]
        );
        WargaProfile::firstOrCreate(
            ['user_id' => $warga->id],
            [
                'nik' => '3577015502950001',
                'alamat' => 'Jl. Salak No. 12, Pandean',
                'rt' => '004',
                'rw' => '002',
                'kelurahan' => 'Pandean',
                'kecamatan' => 'Taman',
                'latitude' => -7.6412000,
                'longitude' => 111.5281000,
                'saldo_poin' => 350,
            ]
        );
    }
}
