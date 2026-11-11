<?php

namespace Database\Seeders;

use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PortalCmsSeeder extends Seeder
{
    /**
     * Seed public portal configuration, announcements, educational articles, and documents.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin_dlh')->first();

        if ($admin === null) {
            return;
        }

        // 1. Portal Settings
        $defaults = PortalSetting::defaults();
        $settingMeta = [
            'hero_kicker' => ['group' => 'hero', 'label' => 'Kicker Hero Portal', 'type' => 'text'],
            'hero_title' => ['group' => 'hero', 'label' => 'Judul Utama Portal', 'type' => 'text'],
            'hero_subtitle' => ['group' => 'hero', 'label' => 'Deskripsi Hero Portal', 'type' => 'textarea'],
            'hero_badge_text' => ['group' => 'hero', 'label' => 'Ringkasan Keunggulan Layanan', 'type' => 'text'],
            'operational_status_label' => ['group' => 'hero', 'label' => 'Status Operasional Kota', 'type' => 'text'],
            'contact_address' => ['group' => 'contact', 'label' => 'Alamat Kantor DLH', 'type' => 'text'],
            'contact_email' => ['group' => 'contact', 'label' => 'Email Resmi DLH', 'type' => 'email'],
            'contact_phone' => ['group' => 'contact', 'label' => 'Telepon Layanan DLH', 'type' => 'text'],
            'transparency_note' => ['group' => 'transparency', 'label' => 'Catatan Transparansi Operasional', 'type' => 'textarea'],
            'announcement_banner_enabled' => ['group' => 'sections', 'label' => 'Tampilkan Banner Pengumuman', 'type' => 'boolean'],
            'show_stats_section' => ['group' => 'sections', 'label' => 'Tampilkan Statistik Kota', 'type' => 'boolean'],
            'show_education_section' => ['group' => 'sections', 'label' => 'Tampilkan Edukasi & Publikasi', 'type' => 'boolean'],
            'show_marketplace_section' => ['group' => 'sections', 'label' => 'Tampilkan Katalog Marketplace', 'type' => 'boolean'],
            'service_steps' => ['group' => 'workflow', 'label' => 'Alur Pelayanan 5 Langkah', 'type' => 'json'],
        ];

        foreach ($settingMeta as $key => $meta) {
            $value = $defaults[$key] ?? null;
            PortalSetting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $meta['group'],
                    'label' => $meta['label'],
                    'type' => $meta['type'],
                    'value' => $meta['type'] === 'json' ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value,
                    'updated_by' => $admin->id,
                ],
            );
        }

        // 2. Pengumuman Resmi Portal
        $announcements = [
            [
                'title' => 'Sosialisasi Jadwal Operasional TPS3R & Penjemputan Pilah Sampah Rumah Tangga',
                'summary' => 'Penyesuaian jam pelayanan loket timbang TPS3R dan rute armada pemilahan tiga kategori.',
                'content' => 'Dinas Lingkungan Hidup Kota Madiun mengajak seluruh rumah tangga untuk memilah sampah organik, anorganik daur ulang, dan residu sebelum jadwal armada penjemputan tiba.',
                'priority' => 'high',
                'status' => 'published',
                'kategori' => 'jadwal',
                'audience_role' => null,
                'show_on_portal' => true,
            ],
            [
                'title' => 'Program Insentif Poin Pemilahan Mandiri di Seluruh Kecamatan',
                'summary' => 'Poin apresiasi warga dapat digunakan untuk potongan retribusi maupun belanja produk daur ulang.',
                'content' => 'Setiap setoran sampah terpilah yang terverifikasi petugas lapangan dan operator TPS3R tercatat otomatis di Buku Kas Poin Warga.',
                'priority' => 'normal',
                'status' => 'published',
                'kategori' => 'informasi',
                'audience_role' => null,
                'show_on_portal' => true,
            ],
        ];

        foreach ($announcements as $item) {
            BroadcastAnnouncement::updateOrCreate(
                ['title' => $item['title']],
                [
                    ...$item,
                    'created_by' => $admin->id,
                    'is_active' => true,
                    'published_at' => now()->subDays(2),
                    'expires_at' => now()->addMonths(6),
                ],
            );
        }

        // 3. Artikel Edukasi & Publikasi Lingkungan
        $articles = [
            [
                'slug' => 'metode-mudah-pemilahan-sampah-organik-rumah-tangga',
                'judul' => 'Metode Mudah Pemilahan Sampah Organik Rumah Tangga Menjadi Pupuk Kompos',
                'kategori' => 'organik',
                'ringkasan' => 'Langkah sederhana mengelola sampah sisa sayur dan buah menggunakan ember tumpuk atau komposter takakura tanpa menimbulkan bau menyengat.',
                'isi' => "Pemilahan sampah organik sejak dari dapur rumah tangga membantu mengurangi timbulan sampah basah yang masuk ke TPA.\n\n1. Siapkan wadah tertutup khusus sisa makanan dan dedaunan.\n2. Tiriskan air berlebih sebelum dimasukkan ke komposter atau disetor.\n3. Campurkan sekam atau serbuk gergaji untuk menjaga kelembapan tetap seimbang.\n4. Setorkan ke TPS3R terdekat atau manfaatkan langsung untuk pekarangan rumah.",
                'penulis_nama' => 'Tim Penyuluh DLH Madiun',
                'penulis_jabatan' => 'Bidang Pengelolaan Sampah',
                'waktu_baca_menit' => 5,
                'is_featured' => true,
            ],
            [
                'slug' => 'mengenal-kode-plastik-1-sampai-7-bank-sampah',
                'judul' => 'Mengenal Kode Plastik 1 sampai 7: Mana yang Diterima di Bank Sampah Madiun?',
                'kategori' => 'plastik_kertas',
                'ringkasan' => 'Pemetaan nilai ekonomi antara botol PET bening, botol HDPE detergen, hingga kemasan plastik sachet multi-layer.',
                'isi' => "Tidak semua plastik memiliki nilai daur ulang yang sama. Memahami simbol segitiga kode resin membantu warga memilah dengan tepat.\n\nKode 1 (PET) dan Kode 2 (HDPE) memiliki nilai tukar tinggi apabila disetorkan dalam kondisi bersih, kering, dan lepas label. Sementara kemasan multi-layer (Kode 7/Other) diarahkan menjadi bahan ecobrick atau penanganan residu.",
                'penulis_nama' => 'Pengelola BSI Madiun Asri',
                'penulis_jabatan' => 'Mitra Bank Sampah Induk',
                'waktu_baca_menit' => 6,
                'is_featured' => false,
            ],
            [
                'slug' => 'langkah-penanganan-sampah-b3-rumah-tangga',
                'judul' => 'Langkah Penanganan Sampah B3 Rumah Tangga (Baterai, Lampu, dan Kemasan Obat)',
                'kategori' => 'b3_rumah_tangga',
                'ringkasan' => 'Protokol pengemasan terpisah zat beracun dan lokasi drop-box khusus agar tidak mencemari air tanah serta saluran drainase.',
                'isi' => "Bahan Berbahaya dan Beracun (B3) skala rumah tangga meliputi baterai bekas, lampu neon/LED, termometer, serta kemasan aerosol.\n\nJangan pernah mencampur limbah B3 ke wadah organik maupun anorganik kering. Simpan dalam wadah tersegel berlabel dan serahkan pada titik pengumpulan khusus di kantor kelurahan atau TPS3R binaan DLH.",
                'penulis_nama' => 'Bidang Pengendalian Pencemaran DLH',
                'penulis_jabatan' => 'Pengawasan Limbah B3',
                'waktu_baca_menit' => 5,
                'is_featured' => false,
            ],
            [
                'slug' => 'rencana-pengurangan-sampah-residu-tpa-winongo',
                'judul' => 'Rencana Pengurangan Sampah Residu Menuju TPA Winongo Kota Madiun',
                'kategori' => 'kebijakan',
                'ringkasan' => 'Optimalisasi pemilahan di tingkat RW serta integrasi layanan TPS3R kelurahan untuk memperpanjang usia layanan TPA.',
                'isi' => 'Pengurangan timbulan residu dari sumber merupakan kunci keberlanjutan kota. Dinas Lingkungan Hidup mengintegrasikan pencatatan manual tervalidasi antara petugas gerobak, operator TPS3R, dan pengawasan dinas secara berkala.',
                'penulis_nama' => 'Sekretariat DLH Kota Madiun',
                'penulis_jabatan' => 'Tata Kelola Lingkungan',
                'waktu_baca_menit' => 6,
                'is_featured' => true,
            ],
        ];

        foreach ($articles as $idx => $article) {
            EducationArticle::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    ...$article,
                    'created_by' => $admin->id,
                    'status' => 'published',
                    'published_at' => now()->subDays(10 - $idx),
                ],
            );
        }

        // 4. Dokumen & Panduan Publik
        Storage::disk('public')->makeDirectory('documents');
        $minimalPdf = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n";

        $documents = [
            [
                'slug' => 'buku-saku-panduan-pemilahan-sampah-madiun',
                'judul' => 'Buku Saku Panduan Pemilahan Sampah Rumah Tangga Kota Madiun',
                'kategori' => 'panduan',
                'deskripsi' => 'Pedoman praktis visual bagi warga, RT/RW, dan kader lingkungan dalam memilah tiga kategori wadah sampah.',
                'file_path' => 'documents/buku-saku-pemilahan-madiun.pdf',
                'file_size_bytes' => 1450000,
                'tanggal_rilis' => '2026-01-10',
            ],
            [
                'slug' => 'peta-sebaran-dan-kontak-tps3r-bank-sampah-madiun',
                'judul' => 'Peta Sebaran dan Direktori Fasilitas TPS3R & Bank Sampah Kota Madiun',
                'kategori' => 'peta',
                'deskripsi' => 'Daftar alamat resmi fasilitas pengolahan, jam layanan loket, serta jenis sampah yang diterima per kecamatan.',
                'file_path' => 'documents/direktori-tps3r-madiun.pdf',
                'file_size_bytes' => 980000,
                'tanggal_rilis' => '2026-01-15',
            ],
            [
                'slug' => 'sop-verifikasi-timbangan-dan-pengolahan-tps3r',
                'judul' => 'SOP Verifikasi Timbangan dan Pengolahan Terpadu TPS3R Kota Madiun',
                'kategori' => 'sop',
                'deskripsi' => 'Standar operasional penerimaan muatan armada, kalibrasi timbangan manual, dan pencatatan fraksi residu.',
                'file_path' => 'documents/sop-tps3r-madiun.pdf',
                'file_size_bytes' => 1220000,
                'tanggal_rilis' => '2026-02-01',
            ],
        ];

        foreach ($documents as $doc) {
            if (! Storage::disk('public')->exists($doc['file_path'])) {
                Storage::disk('public')->put($doc['file_path'], $minimalPdf);
            }

            PublicDocument::updateOrCreate(
                ['slug' => $doc['slug']],
                [
                    ...$doc,
                    'created_by' => $admin->id,
                    'file_format' => 'PDF',
                    'hak_akses_label' => 'Akses Bebas Publik',
                    'status' => 'published',
                ],
            );
        }
    }
}
