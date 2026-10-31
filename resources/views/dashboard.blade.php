<x-layouts::app :title="__('Dashboard Utama')">
    <div class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Welcome Hero Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-emerald-950 via-emerald-900 to-teal-950 p-6 text-white shadow-lg relative overflow-hidden border border-emerald-800/40">
            <div class="absolute -right-10 -bottom-10 size-48 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex items-start sm:items-center gap-4">
                <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white p-1.5 shadow-md shadow-emerald-950/20 ring-2 ring-emerald-300/40">
                    <img src="{{ asset('images/app_logo.png') }}" alt="Logo Kota Madiun" class="size-full object-contain" />
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="inline-flex items-center rounded-full bg-emerald-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-200 border border-emerald-500/30">
                            {{ $user->role->label() }}
                        </span>
                        <span class="text-xs text-emerald-200">Sistem Lingkungan Terpadu Kota Madiun</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, {{ $user->name }}
                    </h1>
                    <p class="text-sm text-emerald-100/90 mt-1 max-w-2xl">
                        Mari bersama wujudkan Kota Madiun bersih, terpilah, dan berkelanjutan menuju zero waste city.
                    </p>
                </div>
            </div>

            <div class="relative z-10 bg-white/10 backdrop-blur-md px-5 py-3 rounded-xl border border-white/20 self-start md:self-auto text-left md:text-right">
                <p class="text-xs text-emerald-200 uppercase font-medium">Hari & Tanggal</p>
                <p class="text-sm font-bold">{{ now()->translatedFormat('l, d F Y') }}</p>
                <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-300 mt-0.5">
                    <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span> Sistem Aktif
                </span>
            </div>
        </div>

        @if ($user->isWarga())
            <!-- ========================================== -->
            <!-- DASHBOARD KHUSUS WARGA                     -->
            <!-- ========================================== -->
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Poin Reward Card -->
                <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-5 shadow-sm dark:border-amber-900/40 dark:from-zinc-900 dark:to-amber-950/20 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">Saldo Poin Reward</span>
                        <span class="flex size-9 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300"><flux:icon.wallet class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400">
                            {{ number_format($saldoPoin, 0, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">Poin</span>
                        </h3>
                        <p class="text-xs text-amber-900/80 dark:text-amber-300/80 mt-1 font-medium">
                            Setara Rp {{ number_format($saldoPoin * 100, 0, ',', '.') }} (Bisa ditukar produk)
                        </p>
                    </div>
                </div>

                <!-- Total Sampah Card -->
                <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-5 shadow-sm dark:border-emerald-900/40 dark:from-zinc-900 dark:to-emerald-950/20 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Sampah Disetor</span>
                        <span class="flex size-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300"><flux:icon.scale class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-400">
                            {{ number_format($totalSampah, 1, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">Kg</span>
                        </h3>
                        <p class="text-xs text-emerald-900/80 dark:text-emerald-300/80 mt-1 font-medium">
                            Akumulasi sampah terpilah rumah tangga
                        </p>
                    </div>
                </div>

                <!-- Pengaduan Status Card -->
                <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-5 shadow-sm dark:border-blue-900/40 dark:from-zinc-900 dark:to-blue-950/20 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-800 dark:text-blue-300">Laporan Pengaduan</span>
                        <span class="flex size-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300"><flux:icon.megaphone class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400">
                            {{ $pengaduanSaya->count() }} <span class="text-sm font-semibold text-zinc-500">Tiket</span>
                        </h3>
                        <p class="text-xs text-blue-900/80 dark:text-blue-300/80 mt-1 font-medium">
                            Aspirasi kebersihan lingkungan Madiun
                        </p>
                    </div>
                </div>

                <!-- Wilayah Domisili Card -->
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Wilayah Domisili</span>
                        <span class="flex size-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            <flux:icon.map-pin class="size-5" />
                        </span>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 truncate">
                            {{ $profile->kecamatan ?? 'Kota Madiun' }}
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            Kel. {{ $profile->kelurahan ?? '-' }} • RT {{ $profile->rt ?? '-' }} / RW {{ $profile->rw ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Warga Detailed Sections (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kolom Kiri: Riwayat Timbangan & Pengaduan (2 spans) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Riwayat Penimbangan Sampah -->
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Riwayat Penimbangan Sampah</h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Daftar penimbangan sampah terpilah oleh petugas lapangan.</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                Terverifikasi
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 font-semibold uppercase">
                                        <th class="py-2.5 px-3">Tanggal</th>
                                        <th class="py-2.5 px-3">Kategori Sampah</th>
                                        <th class="py-2.5 px-3">Berat (Kg)</th>
                                        <th class="py-2.5 px-3">Poin Reward</th>
                                        <th class="py-2.5 px-3">Petugas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @forelse ($riwayatTimbangan as $timbang)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                                            <td class="py-3 px-3 font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $timbang->created_at->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="py-3 px-3 capitalize">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $timbang->jenis_sampah === 'organik' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300' }}">
                                                    {{ $timbang->jenis_sampah }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 font-bold text-zinc-900 dark:text-zinc-100">
                                                {{ number_format($timbang->berat_kg, 1, ',', '.') }} kg
                                            </td>
                                            <td class="py-3 px-3 font-bold text-amber-600 dark:text-amber-400">
                                                +{{ number_format($timbang->poin_didapat, 0, ',', '.') }} Poin
                                            </td>
                                            <td class="py-3 px-3 text-zinc-500 dark:text-zinc-400">
                                                {{ $timbang->petugas->name ?? 'Petugas DLH' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-zinc-400">
                                                Belum ada catatan penimbangan sampah. Setor sampah terpilah saat petugas berkeliling untuk mengumpulkan poin!
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pengaduan Saya -->
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Status Pengaduan Kebersihan</h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Laporan titik sampah liar atau keluhan lingkungan yang Anda sampaikan.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse ($pengaduanSaya as $aduan)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-mono text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $aduan->nomor_tiket }}</span>
                                            <span class="text-xs text-zinc-400">•</span>
                                            <span class="text-xs text-zinc-500">{{ $aduan->created_at->diffForHumans() }}</span>
                                        </div>
                                        <h4 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">{{ $aduan->judul }}</h4>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-1">{{ $aduan->deskripsi }}</p>
                                    </div>
                                    <div class="self-start sm:self-center shrink-0">
                                        @if ($aduan->status === 'selesai')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">Selesai</span>
                                        @elseif ($aduan->status === 'dalam_proses')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">Diproses</span>
                                        @elseif ($aduan->status === 'ditolak')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200">Ditolak</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">Menunggu</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-zinc-400 text-xs">
                                    Belum ada tiket pengaduan aktif. Jika menemukan tumpukan sampah liar, laporkan ke petugas DLH.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Jadwal Penjemputan, Katalog, Mobile Banner (1 span) -->
                <div class="space-y-6">
                    <!-- Jadwal Penjemputan Terdekat -->
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Jadwal Penjemputan</h2>
                            <span class="text-xs text-emerald-700 dark:text-emerald-400 font-semibold">Truk DLH</span>
                        </div>

                        <div class="space-y-3">
                            @forelse ($jadwalWilayah as $jadwal)
                                <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-3.5 text-xs dark:border-zinc-800 dark:bg-zinc-800/50">
                                    <div class="flex items-center justify-between font-semibold text-zinc-900 dark:text-zinc-100">
                                        <span>{{ $jadwal->nama_wilayah_rute ?? $jadwal->id_wilayah_rute }}</span>
                                        <span class="text-emerald-700 dark:text-emerald-400 capitalize">{{ $jadwal->status }}</span>
                                    </div>
                                    <p class="mt-1 flex items-center gap-1.5 text-zinc-500">
                                        <flux:icon.calendar-days class="size-3.5" /> {{ \Illuminate\Support\Carbon::parse($jadwal->tanggal_penjemputan)->translatedFormat('l, d F Y') }}
                                    </p>
                                    <p class="flex items-center gap-1.5 text-zinc-500">
                                        <flux:icon.clock class="size-3.5" /> {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB
                                    </p>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-400 text-center py-4">Belum ada jadwal penjemputan terbit.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Marketplace Daur Ulang Showcase -->
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Produk Daur Ulang TPS3R</h2>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">Poin sampah Anda dapat ditukarkan dengan produk olahan berikut:</p>

                        <div class="grid grid-cols-2 gap-3">
                            @forelse ($produkKatalog as $prod)
                                <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-800/50 flex flex-col justify-between">
                                    <div>
                                        <flux:icon.cube class="mb-1 size-5 text-emerald-700 dark:text-emerald-300" />
                                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 line-clamp-1">{{ $prod->nama_produk }}</h4>
                                        <p class="text-[11px] text-zinc-500">Stok: {{ $prod->stok }}</p>
                                    </div>
                                    <p class="font-extrabold text-emerald-700 dark:text-emerald-400 mt-2">
                                        Rp {{ number_format($prod->harga, 0, ',', '.') }}
                                    </p>
                                </div>
                            @empty
                                <p class="col-span-2 text-xs text-zinc-400 text-center py-3">Belum ada produk daur ulang aktif.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Flutter Mobile Companion Banner -->
                    <div class="rounded-2xl bg-gradient-to-br from-emerald-800 to-teal-900 p-6 text-white shadow-md relative overflow-hidden">
                        <flux:icon.device-phone-mobile class="mb-2 size-7 text-emerald-100" />
                        <h3 class="font-extrabold text-base">Aplikasi Mobile Madiun Terpadu</h3>
                        <p class="text-xs text-emerald-100/90 mt-1 leading-relaxed">
                            Gunakan aplikasi Flutter mobile untuk scan barcode setoran timbangan dan tracking armada sampah secara langsung dari ponsel Anda.
                        </p>
                    </div>
                </div>
            </div>

        @elseif ($user->isPetugasLapangan())
            <!-- ========================================== -->
            <!-- DASHBOARD KHUSUS PETUGAS LAPANGAN          -->
            <!-- ========================================== -->
            @php
                $profile = $petugasData['profile'] ?? null;
                $jadwalTugas = $petugasData['jadwalTugas'] ?? collect();
                $timbanganTerakhir = $petugasData['timbanganTerakhir'] ?? collect();
                $totalTimbanganHariIni = $petugasData['totalTimbanganHariIni'] ?? 0;
            @endphp

            <!-- Petugas Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <span class="text-xs font-semibold uppercase text-zinc-500">Wilayah Rute Tugas</span>
                    <h3 class="text-xl font-black text-emerald-700 dark:text-emerald-400 mt-2 truncate">
                        {{ $profile->id_wilayah_rute ?? 'RUTE-KART-01' }}
                    </h3>
                    <p class="text-xs text-zinc-500 mt-1 truncate">{{ $profile->nama_wilayah_tugas ?? 'Wilayah Madiun' }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <span class="text-xs font-semibold uppercase text-zinc-500">Kendaraan Dinas</span>
                    <h3 class="text-xl font-black text-zinc-900 dark:text-zinc-100 mt-2">
                        {{ $profile->jenis_kendaraan ?? 'Dump Truck' }}
                    </h3>
                    <p class="text-xs text-zinc-500 mt-1">Plat: {{ $profile->plat_nomor ?? 'AE 8123 AA' }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <span class="text-xs font-semibold uppercase text-zinc-500">Timbangan Hari Ini</span>
                    <h3 class="text-xl font-black text-teal-600 dark:text-teal-400 mt-2">
                        {{ number_format($totalTimbanganHariIni, 1, ',', '.') }} <span class="text-xs font-normal">kg</span>
                    </h3>
                    <p class="text-xs text-zinc-500 mt-1">Hasil penimbangan sampah warga</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                    <span class="text-xs font-semibold uppercase text-zinc-500">Status Tugas</span>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-2 capitalize">
                        {{ $profile->status_tugas ?? 'Siap Bertugas' }}
                    </h3>
                    <p class="text-xs text-zinc-500 mt-1">NIP: {{ $profile->nip ?? '-' }}</p>
                </div>
            </div>

            <!-- Petugas Grid Detailed -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Jadwal Penjemputan Rute -->
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-1">Rute Penjemputan Bertugas</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">Jadwal operasional armada truk sampah hari ini dan mendatang.</p>

                    <div class="space-y-3">
                        @forelse ($jadwalTugas as $jadwal)
                            <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 text-xs dark:border-zinc-800 dark:bg-zinc-800/50">
                                <div class="flex items-center justify-between font-bold text-zinc-900 dark:text-zinc-100">
                                    <span>{{ $jadwal->nama_wilayah_rute ?? $jadwal->id_wilayah_rute }}</span>
                                    <span class="capitalize px-2 py-0.5 rounded text-[11px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">{{ $jadwal->status }}</span>
                                </div>
                                <p class="mt-1 flex items-center gap-1.5 text-zinc-500"><flux:icon.calendar-days class="size-3.5" /> {{ \Illuminate\Support\Carbon::parse($jadwal->tanggal_penjemputan)->translatedFormat('l, d F Y') }}</p>
                                <p class="flex items-center gap-1.5 text-zinc-500"><flux:icon.clock class="size-3.5" /> Pukul {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB</p>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400 py-4 text-center">Belum ada jadwal tugas yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Riwayat Penimbangan yang diinput -->
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-1">Timbangan Terakhir Diinput</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">Setoran warga yang telah berhasil ditimbang dan diberi poin.</p>

                    <div class="space-y-3">
                        @forelse ($timbanganTerakhir as $timbang)
                            <div class="flex items-center justify-between rounded-xl border border-zinc-100 bg-zinc-50 p-3.5 text-xs dark:border-zinc-800 dark:bg-zinc-800/50">
                                <div>
                                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100">{{ $timbang->warga->name ?? 'Warga' }}</h4>
                                    <p class="text-[11px] text-zinc-500 capitalize">Jenis: {{ $timbang->jenis_sampah }} • {{ $timbang->created_at->translatedFormat('d M Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-sm text-zinc-900 dark:text-zinc-100">{{ number_format($timbang->berat_kg, 1, ',', '.') }} kg</span>
                                    <p class="text-[11px] text-amber-600 font-semibold">+{{ $timbang->poin_didapat }} Poin</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-400 py-4 text-center">Belum ada timbangan yang dicatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        @else
            <!-- ========================================== -->
            <!-- DASHBOARD GENERAL / FALLBACK               -->
            <!-- ========================================== -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon.sparkles class="mb-3 size-8 text-civic-700 dark:text-civic-300" />
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Pengelolaan Terpadu</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">
                        Kota Madiun mewujudkan integrasi tata kelola persampahan dari hulu ke hilir dengan kolaborasi 5 peran penting.
                    </p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon.arrow-path-rounded-square class="mb-3 size-8 text-civic-700 dark:text-civic-300" />
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Fasilitas TPS3R</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">
                        Tempat Pengolahan Sampah Reuse-Reduce-Recycle siap menampung sampah terpilah untuk diolah menjadi pupuk dan kerajinan bernilai guna.
                    </p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon.device-phone-mobile class="mb-3 size-8 text-civic-700 dark:text-civic-300" />
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Aplikasi Mobile Flutter</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">
                        Didukung aplikasi mobile native untuk kemudahan penjemputan sampah, pencatatan timbangan, dan pelaporan masyarakat.
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
