<x-layouts::app :title="__('Dashboard Utama')">
    <div class="civic-page">
        <x-civic.page-hero
            :eyebrow="$user->role->label()"
            :title="'Selamat Datang, ' . $user->name"
            description="Mari bersama wujudkan Kota Madiun bersih, terpilah, dan berkelanjutan menuju zero waste city."
        >
            <x-slot:actions>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-left backdrop-blur-sm md:text-right">
                    <p class="text-[0.65rem] font-bold uppercase tracking-wider text-civic-200">Hari & Tanggal</p>
                    <p class="mt-0.5 text-sm font-bold text-white">{{ now()->translatedFormat('l, d F Y') }}</p>
                    <span class="mt-0.5 inline-flex items-center gap-1.5 text-xs text-civic-100">
                        <span class="size-2 animate-pulse rounded-full bg-emerald-300"></span> Sistem Aktif
                    </span>
                </div>
            </x-slot:actions>
        </x-civic.page-hero>

        @if ($user->isWarga())
            @php
                $profile = $wargaData['profile'] ?? null;
                $saldoPoin = $profile->saldo_poin ?? 0;
                $totalSampah = $profile->total_sampah_kg ?? 0;
                $riwayatTimbangan = $wargaData['riwayatTimbangan'] ?? collect();
                $pengaduanSaya = $wargaData['pengaduanSaya'] ?? collect();
                $jadwalWilayah = $wargaData['jadwalWilayah'] ?? collect();
                $produkKatalog = $wargaData['produkKatalog'] ?? collect();
            @endphp

            <!-- Warga Metric Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="civic-stat-card border-amber-200/80 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Saldo Poin Reward</span>
                        <span class="flex size-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300"><flux:icon.wallet class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="font-display text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                            {{ number_format($saldoPoin, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-500">Poin</span>
                        </h3>
                        <p class="mt-1 text-xs font-medium text-amber-900/80 dark:text-amber-300/80">
                            Setara Rp {{ number_format($saldoPoin * 100, 0, ',', '.') }} (Bisa ditukar produk)
                        </p>
                    </div>
                </div>

                <div class="civic-stat-card">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sampah Disetor</span>
                        <span class="civic-icon-tile !size-9"><flux:icon.scale class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="font-display text-2xl font-extrabold text-civic-700 dark:text-civic-300">
                            {{ number_format($totalSampah, 1, ',', '.') }} <span class="text-sm font-semibold text-slate-500">Kg</span>
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Akumulasi sampah terpilah rumah tangga
                        </p>
                    </div>
                </div>

                <div class="civic-stat-card">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Laporan Pengaduan</span>
                        <span class="flex size-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300"><flux:icon.megaphone class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="font-display text-2xl font-extrabold text-sky-700 dark:text-sky-400">
                            {{ $pengaduanSaya->count() }} <span class="text-sm font-semibold text-slate-500">Tiket</span>
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Aspirasi kebersihan lingkungan Madiun
                        </p>
                    </div>
                </div>

                <div class="civic-stat-card">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Wilayah Domisili</span>
                        <span class="civic-icon-tile !size-9"><flux:icon.map-pin class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="truncate font-display text-lg font-extrabold text-slate-950 dark:text-white">
                            {{ $profile->kecamatan ?? 'Kota Madiun' }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Kel. {{ $profile->kelurahan ?? '-' }} • RT {{ $profile->rt ?? '-' }} / RW {{ $profile->rw ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Warga Detailed Sections -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="civic-card overflow-hidden">
                        <div class="civic-section-header">
                            <div>
                                <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Riwayat Penimbangan Sampah</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar penimbangan sampah terpilah oleh petugas lapangan.</p>
                            </div>
                            <span class="civic-status-success">Terverifikasi</span>
                        </div>

                        <div class="civic-table-wrap rounded-none border-0">
                            <table class="civic-table text-xs">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kategori Sampah</th>
                                        <th>Berat (Kg)</th>
                                        <th>Poin Reward</th>
                                        <th>Petugas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($riwayatTimbangan as $timbang)
                                        <tr>
                                            <td class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ $timbang->created_at->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="capitalize">
                                                <span class="{{ $timbang->jenis_sampah === 'organik' ? 'civic-status-success' : 'civic-status-info' }}">
                                                    {{ $timbang->jenis_sampah }}
                                                </span>
                                            </td>
                                            <td class="font-bold text-slate-950 dark:text-white">
                                                {{ number_format($timbang->berat_kg, 1, ',', '.') }} kg
                                            </td>
                                            <td class="font-bold text-amber-600 dark:text-amber-400">
                                                +{{ number_format($timbang->poin_didapat, 0, ',', '.') }} Poin
                                            </td>
                                            <td class="text-slate-500 dark:text-slate-400">
                                                {{ $timbang->petugas->name ?? 'Petugas DLH' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-slate-400">
                                                Belum ada catatan penimbangan sampah. Setor sampah terpilah saat petugas berkeliling untuk mengumpulkan poin!
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="civic-card p-5 sm:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Status Pengaduan Kebersihan</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Laporan titik sampah liar atau keluhan lingkungan yang Anda sampaikan.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse ($pengaduanSaya as $aduan)
                                <div class="flex flex-col justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-4 sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-800/40">
                                    <div>
                                        <div class="mb-1 flex items-center gap-2">
                                            <span class="font-mono text-xs font-semibold text-civic-700 dark:text-civic-300">{{ $aduan->nomor_tiket }}</span>
                                            <span class="text-xs text-slate-400">•</span>
                                            <span class="text-xs text-slate-500">{{ $aduan->created_at->diffForHumans() }}</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-950 dark:text-white">{{ $aduan->judul }}</h4>
                                        <p class="mt-0.5 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">{{ $aduan->deskripsi }}</p>
                                    </div>
                                    <div class="shrink-0 self-start sm:self-center">
                                        @if ($aduan->status === 'selesai')
                                            <span class="civic-status-success">Selesai</span>
                                        @elseif ($aduan->status === 'dalam_proses')
                                            <span class="civic-status-warning">Diproses</span>
                                        @elseif ($aduan->status === 'ditolak')
                                            <span class="civic-status-danger">Ditolak</span>
                                        @else
                                            <span class="civic-status-info">Menunggu</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="civic-empty py-6 text-xs">
                                    Belum ada tiket pengaduan aktif. Jika menemukan tumpukan sampah liar, laporkan ke petugas DLH.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="civic-card p-5 sm:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Jadwal Penjemputan</h2>
                            <span class="text-xs font-semibold text-civic-700 dark:text-civic-300">Truk DLH</span>
                        </div>

                        <div class="space-y-3">
                            @forelse ($jadwalWilayah as $jadwal)
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                                    <div class="flex items-center justify-between font-semibold text-slate-950 dark:text-white">
                                        <span>{{ $jadwal->nama_wilayah_rute ?? $jadwal->id_wilayah_rute }}</span>
                                        <span class="capitalize text-civic-700 dark:text-civic-300">{{ $jadwal->status }}</span>
                                    </div>
                                    <p class="mt-1 flex items-center gap-1.5 text-slate-500">
                                        <flux:icon.calendar-days class="size-3.5" /> {{ \Illuminate\Support\Carbon::parse($jadwal->tanggal_penjemputan)->translatedFormat('l, d F Y') }}
                                    </p>
                                    <p class="flex items-center gap-1.5 text-slate-500">
                                        <flux:icon.clock class="size-3.5" /> {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB
                                    </p>
                                </div>
                            @empty
                                <p class="py-4 text-center text-xs text-slate-400">Belum ada jadwal penjemputan terbit.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="civic-card p-5 sm:p-6">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Produk Daur Ulang TPS3R</h2>
                        </div>
                        <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Poin sampah Anda dapat ditukarkan dengan produk olahan berikut:</p>

                        <div class="grid grid-cols-2 gap-3">
                            @forelse ($produkKatalog as $prod)
                                <div class="flex flex-col justify-between rounded-xl border border-slate-100 bg-slate-50/80 p-3 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                                    <div>
                                        <flux:icon.cube class="mb-1 size-5 text-civic-700 dark:text-civic-300" />
                                        <h4 class="line-clamp-1 font-bold text-slate-950 dark:text-white">{{ $prod->nama_produk }}</h4>
                                        <p class="text-xs text-slate-500">Stok: {{ $prod->stok }}</p>
                                    </div>
                                    <p class="mt-2 font-extrabold text-civic-700 dark:text-civic-300">
                                        Rp {{ number_format($prod->harga, 0, ',', '.') }}
                                    </p>
                                </div>
                            @empty
                                <p class="col-span-2 py-3 text-center text-xs text-slate-400">Belum ada produk daur ulang aktif.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="civic-hero !rounded-2xl !p-6">
                        <div class="civic-hero-grid" aria-hidden="true"></div>
                        <flux:icon.device-phone-mobile class="mb-2 size-7 text-civic-200" />
                        <h3 class="font-display text-base font-extrabold text-white">Aplikasi Mobile Madiun Terpadu</h3>
                        <p class="mt-1 text-xs leading-relaxed text-civic-100/90">
                            Gunakan aplikasi Flutter mobile untuk mencatat setoran timbangan dan tracking armada sampah secara langsung dari ponsel Anda.
                        </p>
                    </div>
                </div>
            </div>

        @elseif ($user->isPetugasLapangan())
            @php
                $profile = $petugasData['profile'] ?? null;
                $jadwalTugas = $petugasData['jadwalTugas'] ?? collect();
                $timbanganTerakhir = $petugasData['timbanganTerakhir'] ?? collect();
                $totalTimbanganHariIni = $petugasData['totalTimbanganHariIni'] ?? 0;
            @endphp

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="civic-stat-card">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Wilayah Rute Tugas</span>
                    <h3 class="mt-2 truncate font-display text-xl font-extrabold text-civic-700 dark:text-civic-300">
                        {{ $profile->id_wilayah_rute ?? 'RUTE-KART-01' }}
                    </h3>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $profile->nama_wilayah_tugas ?? 'Wilayah Madiun' }}</p>
                </div>
                <div class="civic-stat-card">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kendaraan Dinas</span>
                    <h3 class="mt-2 font-display text-xl font-extrabold text-slate-950 dark:text-white">
                        {{ $profile->jenis_kendaraan ?? 'Dump Truck' }}
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">Plat: {{ $profile->plat_nomor ?? 'AE 8123 AA' }}</p>
                </div>
                <div class="civic-stat-card">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Timbangan Hari Ini</span>
                    <h3 class="mt-2 font-display text-xl font-extrabold text-civic-700 dark:text-civic-300">
                        {{ number_format($totalTimbanganHariIni, 1, ',', '.') }} <span class="text-xs font-normal">kg</span>
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">Hasil penimbangan sampah warga</p>
                </div>
                <div class="civic-stat-card">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status Tugas</span>
                    <h3 class="mt-2 font-display text-xl font-extrabold capitalize text-emerald-700 dark:text-emerald-400">
                        {{ $profile->status_tugas ?? 'Siap Bertugas' }}
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">NIP: {{ $profile->nip ?? '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="civic-card p-5 sm:p-6">
                    <h2 class="mb-1 font-display text-base font-extrabold text-slate-950 dark:text-white">Rute Penjemputan Bertugas</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Jadwal operasional armada truk sampah hari ini dan mendatang.</p>

                    <div class="space-y-3">
                        @forelse ($jadwalTugas as $jadwal)
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                                <div class="flex items-center justify-between font-bold text-slate-950 dark:text-white">
                                    <span>{{ $jadwal->nama_wilayah_rute ?? $jadwal->id_wilayah_rute }}</span>
                                    <span class="civic-status-success capitalize">{{ $jadwal->status }}</span>
                                </div>
                                <p class="mt-1 flex items-center gap-1.5 text-slate-500"><flux:icon.calendar-days class="size-3.5" /> {{ \Illuminate\Support\Carbon::parse($jadwal->tanggal_penjemputan)->translatedFormat('l, d F Y') }}</p>
                                <p class="flex items-center gap-1.5 text-slate-500"><flux:icon.clock class="size-3.5" /> Pukul {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB</p>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-400">Belum ada jadwal tugas yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="civic-card p-5 sm:p-6">
                    <h2 class="mb-1 font-display text-base font-extrabold text-slate-950 dark:text-white">Timbangan Terakhir Diinput</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Setoran warga yang telah berhasil ditimbang dan diberi poin.</p>

                    <div class="space-y-3">
                        @forelse ($timbanganTerakhir as $timbang)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                                <div>
                                    <h4 class="font-bold text-slate-950 dark:text-white">{{ $timbang->warga->name ?? 'Warga' }}</h4>
                                    <p class="text-xs capitalize text-slate-500">Jenis: {{ $timbang->jenis_sampah }} • {{ $timbang->created_at->translatedFormat('d M Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-extrabold text-slate-950 dark:text-white">{{ number_format($timbang->berat_kg, 1, ',', '.') }} kg</span>
                                    <p class="text-xs font-semibold text-amber-600">+{{ $timbang->poin_didapat }} Poin</p>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-400">Belum ada timbangan yang dicatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="civic-card-interactive p-6">
                    <span class="civic-icon-tile mb-3"><flux:icon.sparkles class="size-5" /></span>
                    <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Pengelolaan Terpadu</h3>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Kota Madiun mewujudkan integrasi tata kelola persampahan dari hulu ke hilir dengan kolaborasi 5 peran penting.
                    </p>
                </div>
                <div class="civic-card-interactive p-6">
                    <span class="civic-icon-tile mb-3"><flux:icon.arrow-path-rounded-square class="size-5" /></span>
                    <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Fasilitas TPS3R</h3>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Tempat Pengolahan Sampah Reuse-Reduce-Recycle siap menampung sampah terpilah untuk diolah menjadi pupuk dan kerajinan bernilai guna.
                    </p>
                </div>
                <div class="civic-card-interactive p-6">
                    <span class="civic-icon-tile mb-3"><flux:icon.device-phone-mobile class="size-5" /></span>
                    <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Aplikasi Mobile Flutter</h3>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Didukung aplikasi mobile native untuk kemudahan penjemputan sampah, pencatatan timbangan, dan pelaporan masyarakat.
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
