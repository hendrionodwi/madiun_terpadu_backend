<x-layouts::app :title="__('Dashboard Admin DLH Kota Madiun')">
    <div class="civic-page">
        <x-civic.page-hero
            eyebrow="Dashboard Admin DLH"
            title="Pusat Kendali Lingkungan Kota Madiun"
            description="Pantau persampahan, layanan warga, kepatuhan lingkungan, dan kinerja fasilitas dalam satu ruang kerja operasional."
        >
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard.admin.reports.export', ['format' => 'print']) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2.5 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-white/20" title="Cetak Berita Acara & Laporan Resmi DLH Format Cetak A4">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak Laporan DLH
                    </a>
                    <a href="{{ route('dashboard.admin.reports.export', ['format' => 'csv']) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2.5 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-white/20" title="Unduh Tabular CSV / Excel Neraca Sampah Kota Madiun">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Export Excel/CSV
                    </a>
                    <a href="{{ route('dashboard.admin.broadcasts') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2.5 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-white/20">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.34 15.84 9 17.18a2.12 2.12 0 0 1-3-3l6.59-6.59a2 2 0 0 1 2.82 0l.18.18a2 2 0 0 1 0 2.82l-2.83 2.83a1 1 0 0 1-1.42-1.42l2.48-2.47" /></svg>
                        Buat pengumuman
                    </a>
                </div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-right backdrop-blur-sm">
                    <p class="text-[0.62rem] font-bold uppercase tracking-wider text-civic-200">Hari ini</p>
                    <p class="mt-0.5 text-sm font-bold text-white">{{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </x-slot:actions>

            <x-slot:meta>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-civic-100/90">
                    <span class="inline-flex items-center gap-2"><span class="size-2 rounded-full bg-emerald-300"></span>Sistem operasional aktif</span>
                    <span>{{ count($wilayah) }} kecamatan terpantau</span>
                    <span>Data diperbarui {{ now()->format('H:i') }} WIB</span>
                </div>
            </x-slot:meta>
        </x-civic.page-hero>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <section aria-labelledby="ringkasan-kota-title">
            <div class="mb-3 flex items-end justify-between gap-4">
                <div>
                    <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.14em] text-civic-700 dark:text-civic-300">Ringkasan hari ini</p>
                    <h2 id="ringkasan-kota-title" class="mt-1 font-display text-lg font-extrabold text-slate-950 dark:text-white">Kondisi layanan kota</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <x-civic.stat-card
                    label="Sampah terkelola"
                    :value="number_format($macro['total_sampah_terkelola_kg'], 1, ',', '.')"
                    suffix="kg"
                    description="Total bersih masuk TPS3R & armada aktif"
                >
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16m-1 0-.87 12.14A2 2 0 0 1 16.14 21H7.86a2 2 0 0 1-1.99-1.86L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3" /></svg></x-slot:icon>
                </x-civic.stat-card>

                <x-civic.stat-card
                    label="Aduan aktif"
                    :value="$macro['total_aduan_aktif']"
                    suffix="tiket"
                    description="Menunggu atau sedang ditindaklanjuti"
                    tone="amber"
                    :href="route('dashboard.admin.complaints')"
                >
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.63 3.57a9 9 0 1 1-4.24 4.24M3 3l6 6m-6 0 6-6" /></svg></x-slot:icon>
                </x-civic.stat-card>

                <x-civic.stat-card
                    label="TPS3R beroperasi"
                    :value="$macro['total_tps3r_aktif']"
                    suffix="fasilitas"
                    description="Fasilitas aktif di seluruh wilayah"
                    tone="sky"
                    :href="route('dashboard.admin.entities')"
                >
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16m4 0V9a2 2 0 0 0-2-2h-2M9 7h2m-2 4h2m-2 4h2" /></svg></x-slot:icon>
                </x-civic.stat-card>

                <x-civic.stat-card
                    label="Laporan lingkungan"
                    :value="$macro['total_laporan_semester']"
                    suffix="dokumen"
                    description="Pelaporan semester dari pemrakarsa"
                    tone="violet"
                    :href="route('dashboard.admin.reports')"
                >
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.63a3.38 3.38 0 0 0-3.38-3.37h-1.5A1.13 1.13 0 0 1 13.5 7.12v-1.5a3.38 3.38 0 0 0-3.38-3.37H8.25m.75 12h6m-6 3h3m-1.5-15H5.62c-.62 0-1.12.5-1.12 1.13v17.25c0 .62.5 1.12 1.12 1.12h12.76c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 0 0-9-9Z" /></svg></x-slot:icon>
                </x-civic.stat-card>

                <x-civic.stat-card
                    label="Marketplace se-Kota"
                    :value="number_format($macro['total_omset_marketplace'], 0, ',', '.')"
                    prefix="Rp "
                    description="{{ $macro['total_pesanan_marketplace'] }} pesanan ({{ $macro['total_produk_marketplace'] }} produk olahan)"
                    tone="teal"
                    :href="route('dashboard.tps3r.orders')"
                >
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg></x-slot:icon>
                </x-civic.stat-card>
            </div>
        </section>

        <x-civic.section-card
            title="Akses cepat operasional"
            description="Masuk langsung ke pekerjaan yang paling sering digunakan saat pengujian dan operasional harian."
            x-data="{ filter: 'semua' }"
        >
            <x-slot:action>
                <div class="civic-filter-rail" aria-label="Filter akses cepat">
                    @foreach ([
                        'semua' => 'Semua',
                        'operasional' => 'Operasional',
                        'pengguna' => 'Pengguna',
                        'sistem' => 'Sistem',
                    ] as $filterKey => $filterLabel)
                        <button
                            type="button"
                            class="civic-filter-chip"
                            x-on:click="filter = '{{ $filterKey }}'"
                            x-bind:class="{ 'civic-filter-chip-active': filter === '{{ $filterKey }}' }"
                            x-bind:aria-pressed="filter === '{{ $filterKey }}'"
                        >
                            {{ $filterLabel }}
                        </button>
                    @endforeach
                </div>
            </x-slot:action>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div x-show="filter === 'semua' || filter === 'operasional'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.complaints')" title="Pengaduan Warga" description="Tinjau laporan, hasil verifikasi petugas, dan finalisasi tindak lanjut." :meta="$macro['total_aduan_aktif'].' aduan aktif'" tone="amber">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.63 3.57a9 9 0 1 1-4.24 4.24M3 3l6 6m-6 0 6-6" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
                <div x-show="filter === 'semua' || filter === 'operasional'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.routes')" title="Rute & Petugas" description="Atur wilayah kerja, waypoint, jadwal, serta penanggung jawab lapangan." :meta="count($wilayah).' kecamatan'" tone="sky">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20.25 3.75 18V5.25L9 7.5m0 12.75 6-3m-6 3V7.5m6 9.75 5.25 2.25V6.75L15 4.5m0 12.75V4.5M15 4.5l-6 3" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
                <div x-show="filter === 'semua' || filter === 'operasional'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.reports')" title="Verifikasi Laporan" description="Periksa dokumen semester SPPL, UKL-UPL, dan AMDAL pemrakarsa." meta="Dokumen lingkungan" tone="violet">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
                <div x-show="filter === 'semua' || filter === 'operasional'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.points')" title="Poin & Pencairan" description="Pantau kas reward dan proses permintaan pencairan warga." meta="Sirkulasi reward">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0 1 15.8 2.1c.98.27 1.95-.47 1.95-1.49V4.64c0-1.02-.97-1.76-1.95-1.49a60.1 60.1 0 0 1-15.8 2.1v13.5ZM8.25 6.75v10.5" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
                <div x-show="filter === 'semua' || filter === 'pengguna'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.users')" title="Akun & Hak Akses" description="Kelola lima role pengguna dan status aktivasi akun." meta="RBAC 5 role">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.1 9.1 0 0 0 3.74-.48 3 3 0 0 0-4.68-2.72m.94 3.2v-.01c0-1.16-.3-2.25-.84-3.19m.84 3.2v.12A12.3 12.3 0 0 1 12 20.25c-2.17 0-4.2-.56-5.97-1.53m0 0A5.98 5.98 0 0 1 12 13.5a5.98 5.98 0 0 1 5.16 2.02M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
                <div x-show="filter === 'semua' || filter === 'pengguna'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.entities')" title="Validasi Entitas" description="Validasi legalitas pemrakarsa dan fasilitas TPS3R Kota Madiun." meta="Legalitas & fasilitas" tone="sky">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M6 21V4.5A1.5 1.5 0 0 1 7.5 3h9A1.5 1.5 0 0 1 18 4.5V21M9 7.5h1.5M9 11.25h1.5m3-3.75H15m-1.5 3.75H15M9 21v-4.5h6V21" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
                <div x-show="filter === 'semua' || filter === 'sistem'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.master-data')" title="Data Master" description="Atur kategori sampah, tarif konversi, dan jenis pengaduan." meta="Konfigurasi sistem" tone="amber">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 6.75h15m-15 0A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25m-15 0v10.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75M9 11.25h6" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
                <div x-show="filter === 'semua' || filter === 'sistem'" x-transition.opacity.duration.200ms>
                    <x-civic.module-link :href="route('dashboard.admin.activity-logs')" title="Audit Trail" description="Telusuri perubahan dan aktivitas pengguna pada sistem." meta="Jejak aktivitas" tone="rose">
                        <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
                    </x-civic.module-link>
                </div>
            </div>
        </x-civic.section-card>

        <x-civic.section-card
            title="Kinerja lintas wilayah"
            description="Perbandingan volume sampah terpilah dan penyelesaian aduan pada tiga kecamatan."
        >
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                @foreach ($wilayah as $data)
                    @php
                        $percent = $data['total_aduan'] > 0
                            ? round(($data['total_aduan_selesai'] / $data['total_aduan']) * 100)
                            : 100;
                    @endphp
                    <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-linear-to-br from-slate-50 via-white to-civic-50/70 p-5 transition duration-300 hover:-translate-y-1 hover:border-civic-300 hover:shadow-[0_22px_48px_-32px_rgba(4,106,88,0.35)] dark:border-slate-800 dark:from-slate-800/70 dark:via-slate-900 dark:to-civic-950/25 dark:hover:border-civic-700">
                        <span class="absolute -right-3 -top-6 font-display text-7xl font-black text-civic-900/[0.035] transition duration-300 group-hover:scale-110 dark:text-white/[0.035]" aria-hidden="true">0{{ $loop->iteration }}</span>
                        <div class="relative flex items-center justify-between gap-3">
                            <h3 class="font-display text-sm font-extrabold text-slate-950 dark:text-white">{{ $data['nama'] }}</h3>
                            <span class="civic-status-success">Aktif</span>
                        </div>
                        <dl class="relative mt-5 grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <dt class="text-slate-500">Sampah terpilah</dt>
                                <dd class="mt-1 font-display text-lg font-extrabold text-slate-950 dark:text-white">{{ number_format($data['total_sampah_kg'], 1, ',', '.') }} kg</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Aduan selesai</dt>
                                <dd class="mt-1 font-display text-lg font-extrabold text-civic-700 dark:text-civic-300">{{ $data['total_aduan_selesai'] }}/{{ $data['total_aduan'] }}</dd>
                            </div>
                        </dl>
                        <div class="relative mt-5">
                            <div class="mb-1.5 flex justify-between text-[0.68rem] font-bold text-slate-500">
                                <span>Tingkat penyelesaian</span>
                                <span>{{ $percent }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-linear-to-r from-civic-500 to-civic-700 transition-[width] duration-700" style="width: {{ min(100, $percent) }}%"></div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </x-civic.section-card>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-civic.section-card title="Status Pengaduan Masyarakat" description="Laporan kebersihan warga yang paling baru masuk.">
                <x-slot:action><a href="{{ route('dashboard.admin.complaints') }}" class="civic-link">Lihat semua <span aria-hidden="true">&rarr;</span></a></x-slot:action>
                <div class="space-y-1">
                    @forelse ($pengaduanTerbaru as $aduan)
                        <div class="flex items-start justify-between gap-4 rounded-xl px-3 py-3 hover:bg-civic-50 dark:hover:bg-civic-950/30">
                            <div class="min-w-0">
                                <p class="font-mono text-[0.68rem] font-bold text-civic-700 dark:text-civic-300">{{ $aduan->nomor_tiket }}</p>
                                <h3 class="mt-1 truncate font-display text-sm font-bold text-slate-950 dark:text-white">{{ $aduan->judul }}</h3>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $aduan->pelapor->name ?? 'Warga' }} &middot; {{ $aduan->kelurahan ?? $aduan->kecamatan ?? 'Kota Madiun' }}</p>
                            </div>
                            <span @class([
                                'civic-status-success' => $aduan->status === 'selesai',
                                'civic-status-warning' => $aduan->status === 'dalam_proses',
                                'civic-status-info' => ! in_array($aduan->status, ['selesai', 'dalam_proses'], true),
                            ])>{{ ucfirst(str_replace('_', ' ', $aduan->status)) }}</span>
                        </div>
                    @empty
                        <div class="civic-empty">Belum ada pengaduan masyarakat.</div>
                    @endforelse
                </div>
            </x-civic.section-card>

            <x-civic.section-card title="Verifikasi Dokumen Lingkungan" description="Laporan semester pemrakarsa yang membutuhkan perhatian DLH.">
                <x-slot:action><a href="{{ route('dashboard.admin.reports') }}" class="civic-link">Lihat semua <span aria-hidden="true">&rarr;</span></a></x-slot:action>
                <div class="space-y-1">
                    @forelse ($laporanSemesterMenunggu as $lap)
                        <div class="flex items-start justify-between gap-4 rounded-xl px-3 py-3 hover:bg-civic-50 dark:hover:bg-civic-950/30">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md bg-violet-100 px-2 py-0.5 text-[0.65rem] font-extrabold text-violet-800 dark:bg-violet-950/60 dark:text-violet-300">{{ $lap->jenis_dokumen }}</span>
                                    <span class="text-[0.68rem] text-slate-500">{{ $lap->periode_semester }} {{ $lap->tahun }}</span>
                                </div>
                                <h3 class="mt-1.5 truncate font-display text-sm font-bold text-slate-950 dark:text-white">{{ $lap->judul_laporan }}</h3>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $lap->pemrakarsa->name ?? 'Pemrakarsa usaha' }}</p>
                            </div>
                            <span @class([
                                'civic-status-success' => $lap->status === 'disetujui',
                                'civic-status-danger' => $lap->status === 'perlu_revisi',
                                'civic-status-warning' => ! in_array($lap->status, ['disetujui', 'perlu_revisi'], true),
                            ])>{{ ucfirst(str_replace('_', ' ', $lap->status)) }}</span>
                        </div>
                    @empty
                        <div class="civic-empty">Tidak ada laporan yang menunggu verifikasi.</div>
                    @endforelse
                </div>
            </x-civic.section-card>
        </div>

        <x-civic.section-card
            title="Aktivitas Transaksi Marketplace TPS3R se-Kota"
            description="Pantau pesanan produk hasil olahan daur ulang sampah dari seluruh fasilitas TPS3R Kota Madiun."
        >
            <x-slot:action>
                <a href="{{ route('dashboard.tps3r.orders') }}" class="civic-link">Lihat semua pesanan <span aria-hidden="true">&rarr;</span></a>
            </x-slot:action>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-[0.68rem] font-extrabold uppercase tracking-wider text-slate-500 dark:border-slate-800">
                            <th class="pb-3 pl-3">Nomor Pesanan</th>
                            <th class="pb-3">Pembeli</th>
                            <th class="pb-3">Rincian Barang</th>
                            <th class="pb-3">Total Transaksi</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 pr-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse ($pesananTerbaru as $pesanan)
                            <tr class="hover:bg-civic-50/50 dark:hover:bg-civic-950/20">
                                <td class="py-3 pl-3 font-mono font-bold text-civic-700 dark:text-civic-300">
                                    {{ $pesanan->nomor_pesanan }}
                                    <span class="block font-sans text-[0.65rem] font-normal text-slate-400">
                                        {{ $pesanan->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 font-medium text-slate-900 dark:text-slate-100">
                                    {{ $pesanan->buyer->name ?? 'Warga' }}
                                </td>
                                <td class="max-w-xs truncate py-3 text-slate-600 dark:text-slate-300">
                                    {{ $pesanan->items->map(fn ($item) => ($item->product->nama_produk ?? 'Produk').' (x'.$item->jumlah.')')->join(', ') ?: '-' }}
                                </td>
                                <td class="py-3 font-display font-extrabold text-slate-900 dark:text-white">
                                    Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                                </td>
                                <td class="py-3">
                                    <span @class([
                                        'civic-status-success' => in_array($pesanan->status, ['completed', 'selesai', 'paid', 'delivered'], true),
                                        'civic-status-warning' => in_array($pesanan->status, ['pending', 'menunggu_pembayaran'], true),
                                        'civic-status-danger' => in_array($pesanan->status, ['dibatalkan', 'cancelled'], true),
                                        'civic-status-info' => ! in_array($pesanan->status, ['completed', 'selesai', 'paid', 'delivered', 'pending', 'menunggu_pembayaran', 'dibatalkan', 'cancelled'], true),
                                    ])>{{ ucfirst(str_replace('_', ' ', $pesanan->status)) }}</span>
                                </td>
                                <td class="py-3 pr-3 text-right">
                                    <a href="{{ route('dashboard.tps3r.orders.show', $pesanan) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-[0.7rem] font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="civic-empty py-6 text-center">
                                    Belum ada transaksi pesanan marketplace daur ulang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-civic.section-card>
    </div>
</x-layouts::app>
