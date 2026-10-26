<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AdminDlhProfile;
use App\Models\Complaint;
use App\Models\ComplaintVerification;
use App\Models\EnvironmentalReport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\PetugasLapanganProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use App\Models\WasteWeighing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
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
        $tps3rProfile = PengelolaTps3rProfile::firstOrCreate(
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
                'midtrans_merchant_id' => 'G123456789',
                'midtrans_client_key' => 'SB-Mid-client-sample-key-madiun',
                'midtrans_server_key' => 'SB-Mid-server-sample-key-madiun',
                'midtrans_is_production' => false,
                'midtrans_is_active' => true,
            ]
        );

        // 4. Pemrakarsa
        $pemrakarsa = User::firstOrCreate(
            ['email' => 'pemrakarsa@madiunkota.go.id'],
            [
                'name' => 'PT Madiun Indah Plaza (Pemrakarsa)',
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

        // ==========================================
        // Data Sampel Dashboard (Demostrasi Realistis)
        // ==========================================

        // Timbangan Sampah
        WasteWeighing::firstOrCreate(
            ['catatan' => 'Timbangan pilah kardus dan botol plastik RT 04'],
            [
                'warga_id' => $warga->id,
                'petugas_id' => $petugas->id,
                'jenis_sampah' => 'anorganik_plastik',
                'berat_kg' => 12.5,
                'poin_per_kg' => 100,
                'poin_didapat' => 1250,
            ]
        );

        // Setoran Sampah TPS3R
        Tps3rWasteDeposit::firstOrCreate(
            ['nomor_resi' => 'DEP-20261006-KT01'],
            [
                'pengelola_id' => $pengelola->id,
                'tps3r_profile_id' => $tps3rProfile->id,
                'petugas_id' => $petugas->id,
                'sumber_sampah' => 'armada_kelurahan',
                'jenis_sampah' => 'organik',
                'berat_kg' => 420.50,
                'tanggal_setor' => Carbon::today()->toDateString(),
                'catatan' => 'Sampah organik daun dan pasar tradisional Pandean',
            ]
        );

        Tps3rWasteDeposit::firstOrCreate(
            ['nomor_resi' => 'DEP-20261006-KT02'],
            [
                'pengelola_id' => $pengelola->id,
                'tps3r_profile_id' => $tps3rProfile->id,
                'petugas_id' => $petugas->id,
                'sumber_sampah' => 'warga_mandiri',
                'jenis_sampah' => 'anorganik_daur_ulang',
                'berat_kg' => 180.00,
                'tanggal_setor' => Carbon::today()->toDateString(),
                'catatan' => 'Setoran botol PET dan kardus terpilah warga',
            ]
        );

        // Produk Daur Ulang Marketplace
        $prod1 = WasteProduct::firstOrCreate(
            ['nama_produk' => 'Pupuk Kompos Organik Super 5Kg'],
            [
                'seller_id' => $pengelola->id,
                'deskripsi' => 'Pupuk kompos organik hasil fermentasi sampah daun & sayuran TPS3R Kartoharjo. Cocok untuk tanaman hias dan sayur pekarangan.',
                'kategori' => 'pupuk_kompos',
                'harga' => 20000.00,
                'stok' => 45,
                'is_available' => true,
            ]
        );

        $prod2 = WasteProduct::firstOrCreate(
            ['nama_produk' => 'Tas Belanja Anyaman Plastik Daur Ulang'],
            [
                'seller_id' => $pengelola->id,
                'deskripsi' => 'Tas belanja ramah lingkungan kuat dan modis dari bungkus plastik daur ulang binaan KSM TPS3R.',
                'kategori' => 'kerajinan_plastik',
                'harga' => 35000.00,
                'stok' => 20,
                'is_available' => true,
            ]
        );

        // Pesanan Marketplace
        $order = Order::firstOrCreate(
            ['nomor_pesanan' => 'ORD-20261006-001'],
            [
                'buyer_id' => $warga->id,
                'total_harga' => 55000.00,
                'metode_pembayaran' => 'poin_sampah',
                'status' => 'dibayar',
                'alamat_pengiriman' => 'Jl. Salak No. 12, Pandean Kota Madiun',
                'catatan' => 'Mohon dikirim sore hari setelah jam kerja',
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $prod1->id],
            [
                'jumlah' => 1,
                'harga_satuan' => 20000.00,
                'subtotal' => 20000.00,
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $prod2->id],
            [
                'jumlah' => 1,
                'harga_satuan' => 35000.00,
                'subtotal' => 35000.00,
            ]
        );

        // Pengaduan Masyarakat
        $complaint1 = Complaint::firstOrCreate(
            ['nomor_tiket' => 'TKT-20261006-KTH01'],
            [
                'pelapor_id' => $warga->id,
                'judul' => 'Tumpukan Sampah Liar di Saluran Irigasi Kartoharjo',
                'deskripsi' => 'Banyak sampah plastik dan ranting menyumbat saluran irigasi sawah dekat perlintasan rel kereta.',
                'lokasi' => 'Jl. Sedap Malam RT 03/RW 01',
                'kelurahan' => 'Kartanegara',
                'kecamatan' => 'Kartoharjo',
                'status' => 'dalam_proses',
            ]
        );

        ComplaintVerification::firstOrCreate(
            ['complaint_id' => $complaint1->id],
            [
                'petugas_id' => $petugas->id,
                'status_verifikasi' => 'valid',
                'catatan_petugas' => 'Petugas telah meninjau lokasi. Armada pengangkut dijadwalkan membersihkan besok pagi.',
                'tanggal_verifikasi' => Carbon::now()->subHours(2),
            ]
        );

        Complaint::firstOrCreate(
            ['nomor_tiket' => 'TKT-20261006-TMN01'],
            [
                'pelapor_id' => $warga->id,
                'judul' => 'Kontainer Sampah TPS Overload Menumpuk ke Jalan',
                'deskripsi' => 'Kontainer sampah penuh dan berserakan ke bahu jalan raya mengganggu lalu lintas.',
                'lokasi' => 'Dekat Pasar Sleko Madiun',
                'kelurahan' => 'Pandean',
                'kecamatan' => 'Taman',
                'status' => 'menunggu_verifikasi',
            ]
        );

        // Laporan Semester Pemrakarsa
        EnvironmentalReport::firstOrCreate(
            ['judul_laporan' => 'Laporan Pelaksanaan RKL-RPL Semester I Tahun 2026'],
            [
                'pemrakarsa_id' => $pemrakarsa->id,
                'periode_semester' => 'Semester 1 (Jan - Jun)',
                'tahun' => 2026,
                'jenis_dokumen' => 'AMDAL',
                'timbulan_sampah_kg' => 4500.00,
                'metode_pengelolaan' => 'Pemilahan sampah organik & anorganik terpusat, pengomposan mandiri, dan pengangkutan residu bekerjasama dengan DLH Kota Madiun.',
                'file_path' => 'environmental_reports/sample_laporan.pdf',
                'status' => 'menunggu_verifikasi',
            ]
        );
    }
}
