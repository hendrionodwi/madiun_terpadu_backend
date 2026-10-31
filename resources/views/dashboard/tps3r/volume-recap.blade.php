<x-layouts::app :title="__('Rekap Volume Sampah per Kategori')">
    <div x-data="{
        detailModalOpen: false,
        activeSetoran: null,
        previewModalOpen: false,
        previewPhotoUrl: null,
        previewPhotoTitle: '',
        openDetail(setoran) {
            this.activeSetoran = setoran;
            this.detailModalOpen = true;
        },
        openPhoto(url, title) {
            this.previewPhotoUrl = url;
            this.previewPhotoTitle = title || 'Foto Bukti Timbangan Sampah';
            this.previewModalOpen = true;
        }
    }" class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-2 md:p-4">
        <!-- Header Page -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-0.5 text-xs font-semibold text-teal-800 dark:bg-teal-950/70 dark:text-teal-300">
                        {{ $profile->kode_tps3r ?? 'TPS3R' }}
                    </span>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Analitik & Neraca Sampah Fasilitas</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Rekap Volume Sampah per Kategori
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Monitoring agregat tonase masuk, komposisi jenis sampah, dan utilisasi kapasitas di {{ $profile->nama_tps3r ?? 'Fasilitas TPS3R' }}.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'print'])) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3.5 py-2 text-xs font-semibold text-teal-800 shadow-sm transition hover:bg-teal-100 dark:border-teal-800 dark:bg-teal-950/60 dark:text-teal-300 dark:hover:bg-teal-900/50" title="Cetak Berita Acara Resmi Format A4">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak Berita Acara
                </a>
                <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700" title="Unduh Tabular CSV / Excel Neraca Sampah">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Export CSV
                </a>
                <a href="{{ route('dashboard.tps3r.deposits') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Input Setoran
                </a>
                <a href="{{ route('dashboard.tps3r.processing.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-800">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Proses Pengolahan
                </a>
            </div>
        </div>

        <!-- Filter Periode & Parameter -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('dashboard.tps3r.volume-recap') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Dari Tanggal
                    <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-teal-600 focus:ring-teal-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Sampai Tanggal
                    <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-teal-600 focus:ring-teal-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Jenis Sampah
                    <select name="jenis_sampah" class="rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-teal-600 focus:ring-teal-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">Semua Kategori</option>
                        <option value="organik" @selected($selectedJenis === 'organik')>Organik</option>
                        <option value="anorganik_daur_ulang" @selected($selectedJenis === 'anorganik_daur_ulang')>Anorganik Daur Ulang</option>
                        <option value="residu" @selected($selectedJenis === 'residu')>Residu</option>
                    </select>
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Sumber Sampah
                    <select name="sumber_sampah" class="rounded-xl border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-teal-600 focus:ring-teal-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">Semua Sumber</option>
                        <option value="armada_kelurahan" @selected($selectedSumber === 'armada_kelurahan')>Armada Kelurahan</option>
                        <option value="warga_mandiri" @selected($selectedSumber === 'warga_mandiri')>Warga Mandiri</option>
                        <option value="pemrakarsa_komersial" @selected($selectedSumber === 'pemrakarsa_komersial')>Pemrakarsa / Komersial</option>
                    </select>
                </label>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-teal-800">
                        Filter Data
                    </button>
                    <a href="{{ route('dashboard.tps3r.volume-recap') }}" class="rounded-xl border border-zinc-300 px-3 py-2.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800" title="Reset filter ke bulan berjalan">
                        Reset
                    </a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium text-zinc-500">Preset Cepat:</span>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                        Hari Ini
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                        7 Hari Terakhir
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                        Bulan Berjalan
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                        Tahun Ini
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'print'])) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800 transition hover:bg-teal-100 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-300">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak Laporan Sesuai Filter
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Unduh CSV Sesuai Filter
                    </a>
                </div>
            </div>
        </section>

        <!-- KPI Metric Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Tonase -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Volume Masuk</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300"><flux:icon.scale class="size-5" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-teal-700 dark:text-teal-400">
                        {{ number_format($totalVolumeKg, 1, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">kg</span>
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500">
                        Setara <strong>{{ number_format($totalVolumeTon, 2, ',', '.') }} Ton</strong> ({{ $totalSetoranCount }} rit/setoran)
                    </p>
                </div>
            </div>

            <!-- Organik -->
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Sampah Organik</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300"><flux:icon.sparkles class="size-5" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-400">
                        {{ number_format($organikKg, 1, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">kg</span>
                    </h3>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="inline-block rounded bg-emerald-200/70 px-1.5 py-0.5 text-[11px] font-bold text-emerald-900 dark:bg-emerald-900/70 dark:text-emerald-200">
                            {{ $organikPct }}%
                        </span>
                        <span class="text-xs text-zinc-500">Kompos & Pakan Maggot</span>
                    </div>
                </div>
            </div>

            <!-- Anorganik Daur Ulang -->
            <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-800 dark:text-blue-300">Anorganik Daur Ulang</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300"><flux:icon.arrow-path-rounded-square class="size-5" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-blue-700 dark:text-blue-400">
                        {{ number_format($anorganikKg, 1, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">kg</span>
                    </h3>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="inline-block rounded bg-blue-200/70 px-1.5 py-0.5 text-[11px] font-bold text-blue-900 dark:bg-blue-900/70 dark:text-blue-200">
                            {{ $anorganikPct }}%
                        </span>
                        <span class="text-xs text-zinc-500">Bank Sampah & Marketplace</span>
                    </div>
                </div>
            </div>

            <!-- Residu ke TPA -->
            <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5 shadow-sm dark:border-rose-900/50 dark:bg-rose-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-800 dark:text-rose-300">Residu (TPA)</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300"><flux:icon.trash class="size-5" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-rose-700 dark:text-rose-400">
                        {{ number_format($residuKg, 1, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">kg</span>
                    </h3>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="inline-block rounded bg-rose-200/70 px-1.5 py-0.5 text-[11px] font-bold text-rose-900 dark:bg-rose-900/70 dark:text-rose-200">
                            {{ $residuPct }}%
                        </span>
                        <span class="text-xs text-zinc-500">Pengangkutan ke TPA</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Komposisi & Utilisasi Kapasitas -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Komposisi Sampah -->
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Proporsi Neraca Sampah
                    </h2>
                    <span class="text-xs text-zinc-500">Target Diversi Sampah Kota</span>
                </div>

                <!-- Multi-segment Progress Bar -->
                <div class="mt-5">
                    <div class="flex h-5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        @if ($totalVolumeKg > 0)
                            <div style="width: {{ $organikPct }}%" class="bg-emerald-500 transition-all duration-500" title="Organik: {{ $organikPct }}%"></div>
                            <div style="width: {{ $anorganikPct }}%" class="bg-blue-500 transition-all duration-500" title="Anorganik: {{ $anorganikPct }}%"></div>
                            <div style="width: {{ $residuPct }}%" class="bg-rose-500 transition-all duration-500" title="Residu: {{ $residuPct }}%"></div>
                        @else
                            <div class="w-full bg-zinc-200 text-center text-[10px] text-zinc-500 dark:bg-zinc-800">Belum ada data</div>
                        @endif
                    </div>

                    <!-- Legend Items -->
                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-2.5 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                            <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                <span class="size-2 rounded-full bg-emerald-500"></span> Organik
                            </div>
                            <p class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $organikPct }}%</p>
                            <p class="text-[11px] text-zinc-500">{{ number_format($organikKg, 0, ',', '.') }} kg</p>
                        </div>

                        <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
                            <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-800 dark:text-blue-300">
                                <span class="size-2 rounded-full bg-blue-500"></span> Anorganik
                            </div>
                            <p class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $anorganikPct }}%</p>
                            <p class="text-[11px] text-zinc-500">{{ number_format($anorganikKg, 0, ',', '.') }} kg</p>
                        </div>

                        <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-2.5 dark:border-rose-900/40 dark:bg-rose-950/20">
                            <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-rose-800 dark:text-rose-300">
                                <span class="size-2 rounded-full bg-rose-500"></span> Residu
                            </div>
                            <p class="mt-1 text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $residuPct }}%</p>
                            <p class="text-[11px] text-zinc-500">{{ number_format($residuKg, 0, ',', '.') }} kg</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-start gap-2 rounded-xl bg-zinc-50 p-3 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon.light-bulb class="size-4 shrink-0" />
                    <span><strong>Tingkat Reduksi Sampah:</strong> Sebanyak <strong>{{ round($organikPct + $anorganikPct, 1) }}%</strong> sampah berhasil dialihkan dari Tempat Pemrosesan Akhir (TPA) untuk diolah kembali di TPS3R.</span>
                </div>
            </section>

            <!-- Utilisasi Kapasitas Fasilitas -->
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Kapasitas & Beban Fasilitas
                    </h2>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        Kapasitas: {{ number_format($kapasitasHarian, 0, ',', '.') }} kg/hari
                    </span>
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                        <span>Rata-rata Masuk: {{ number_format($rataRataHarianKg, 1, ',', '.') }} kg/hari</span>
                        <span class="font-bold text-teal-700 dark:text-teal-400">{{ $persenUtilisasi }}% Kapasitas</span>
                    </div>

                    <div class="mt-2 h-4 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div style="width: {{ min(100, $persenUtilisasi) }}%" class="h-full {{ $persenUtilisasi > 90 ? 'bg-amber-500' : 'bg-teal-600' }} rounded-full transition-all duration-500"></div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <span class="text-xs text-zinc-500">Daya Tampung Tersisa:</span>
                            <p class="mt-0.5 font-bold text-zinc-900 dark:text-zinc-100">
                                {{ number_format(max(0, $kapasitasHarian - $rataRataHarianKg), 1, ',', '.') }} kg / hari
                            </p>
                        </div>
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <span class="text-xs text-zinc-500">Status Operasional:</span>
                            <p class="mt-0.5 font-bold {{ $persenUtilisasi > 90 ? 'text-amber-600' : 'text-emerald-600' }}">
                                {{ $persenUtilisasi > 90 ? 'Mendekati Batas' : 'Kapasitas Prima' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Breakdown Sumber Sampah -->
                <div class="mt-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                    <span class="text-xs font-semibold text-zinc-500">Kontribusi per Sumber:</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse($breakdownSumber as $sumber)
                            @php
                                $pctSumber = $totalVolumeKg > 0 ? round(($sumber->total_kg / $totalVolumeKg) * 100, 1) : 0;
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                <span class="capitalize font-semibold">{{ str_replace('_', ' ', $sumber->sumber_sampah) }}:</span>
                                <span>{{ number_format($sumber->total_kg, 1, ',', '.') }} kg ({{ $pctSumber }}%)</span>
                            </span>
                        @empty
                            <span class="text-xs text-zinc-400">Belum ada distribusi sumber tercatat.</span>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <!-- Rekap Harian 14 Hari Terakhir -->
        @if ($dailyRecords->isNotEmpty())
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Tren Agregat Harian (14 Hari Terakhir dalam Periode)
                    </h2>
                    <span class="text-xs text-zinc-500">Beban per tanggal setor</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-3">Tanggal Setor</th>
                                <th class="px-4 py-3 text-center">Rit / Ritasi Masuk</th>
                                <th class="px-4 py-3 text-right">Total Berat (kg)</th>
                                <th class="px-4 py-3 text-right">Beban Kapasitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($dailyRecords as $record)
                                @php
                                    $dayPct = $kapasitasHarian > 0 ? min(100, round(($record->total_kg / $kapasitasHarian) * 100, 1)) : 0;
                                @endphp
                                <tr class="text-zinc-700 dark:text-zinc-200">
                                    <td class="px-4 py-3 font-medium">
                                        {{ \Carbon\Carbon::parse($record->tanggal_setor)->translatedFormat('l, d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $record->total_rit }} rit
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-teal-700 dark:text-teal-400">
                                        {{ number_format($record->total_kg, 1, ',', '.') }} kg
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="text-xs font-semibold {{ $dayPct > 90 ? 'text-amber-600' : 'text-emerald-600' }}">
                                                {{ $dayPct }}%
                                            </span>
                                            <div class="h-2 w-16 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                <div style="width: {{ $dayPct }}%" class="h-full {{ $dayPct > 90 ? 'bg-amber-500' : 'bg-emerald-500' }}"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <!-- Rincian Data Setoran Sampah Terfilter -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Log Riwayat Setoran Sampah
                    </h2>
                    <p class="text-xs text-zinc-500">Rincian resi dan timbangan setoran yang terdata pada periode filter.</p>
                </div>
                <span class="text-xs font-semibold text-zinc-500">
                    Menampilkan {{ $riwayatSetoran->total() }} entri
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Nomor Resi</th>
                            <th class="px-4 py-3">Sumber Sampah</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-right">Berat (kg)</th>
                            <th class="px-4 py-3">Penerima/Petugas</th>
                            <th class="px-4 py-3 text-center">Muatan Warga</th>
                            <th class="px-4 py-3">Catatan</th>
                            <th class="px-4 py-3 text-center">Aksi Laporan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($riwayatSetoran as $setoran)
                            @php
                                $setoranPayload = [
                                    'id' => $setoran->id,
                                    'nomor_resi' => $setoran->nomor_resi,
                                    'tanggal_setor' => $setoran->tanggal_setor->format('d M Y'),
                                    'petugas' => $setoran->petugas?->name ?? ($setoran->pengelola?->name ?? 'Mandiri / Pengelola'),
                                    'sumber_sampah' => $setoran->sumber_sampah,
                                    'route_code' => $setoran->route_code,
                                    'jenis_sampah' => $setoran->jenis_sampah,
                                    'berat_kg' => (float) $setoran->berat_kg,
                                    'status' => $setoran->status,
                                    'catatan' => $setoran->catatan,
                                    'weighings' => $setoran->weighings->map(fn($w) => [
                                        'id' => $w->id,
                                        'warga_name' => $w->warga?->name ?? 'Warga Kota Madiun',
                                        'warga_phone' => $w->warga?->phone_number ?? '-',
                                        'jenis_sampah' => $w->jenis_sampah,
                                        'berat_kg' => (float) $w->berat_kg,
                                        'poin_didapat' => $w->poin_didapat,
                                        'foto_bukti' => $w->foto_bukti ? (str_starts_with($w->foto_bukti, 'http') ? $w->foto_bukti : asset('storage/' . $w->foto_bukti)) : null,
                                        'catatan' => $w->catatan,
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="text-zinc-700 dark:text-zinc-200">
                                <td class="px-4 py-3 font-medium whitespace-nowrap">
                                    {{ $setoran->tanggal_setor->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-teal-700 dark:text-teal-400">
                                    {{ $setoran->nomor_resi }}
                                </td>
                                <td class="px-4 py-3 capitalize whitespace-nowrap">
                                    {{ str_replace('_', ' ', $setoran->sumber_sampah) }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badgeClass = match($setoran->jenis_sampah) {
                                            'organik' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                                            'anorganik_daur_ulang' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                                            default => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $badgeClass }}">
                                        {{ str_replace('_', ' ', $setoran->jenis_sampah) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($setoran->berat_kg, 1, ',', '.') }} kg
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $setoran->petugas?->name ?? $setoran->pengelola?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($setoran->weighings->isNotEmpty())
                                        <button type="button" @click='openDetail(@json($setoranPayload))' class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition shadow-sm">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>{{ $setoran->weighings->count() }} Warga</span>
                                        </button>
                                    @else
                                        <span class="text-xs text-zinc-400">Direct Truk</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-zinc-500">
                                    {{ $setoran->catatan ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="{{ route('dashboard.tps3r.deposits.show', $setoran) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-300 dark:border-teal-700 bg-teal-50 dark:bg-teal-950/50 px-2.5 py-1 text-xs font-bold text-teal-700 dark:text-teal-300 hover:bg-teal-100 transition shadow-sm">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Detail Sampah</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data setoran sampah pada rentang filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $riwayatSetoran->links() }}
            </div>
        </section>

        <!-- Modal Detail Laporan Sampah (Warga, Berat & Foto Bukti) -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @keydown.escape.window="detailModalOpen = false">
            <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="detailModalOpen = false">
                <!-- Header Modal -->
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/50">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <span>Detail Laporan Sampah Warga</span>
                            <span class="font-mono text-xs px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300 font-semibold" x-text="activeSetoran ? activeSetoran.nomor_resi : ''"></span>
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            Penyetor/Petugas: <span class="font-semibold text-zinc-700 dark:text-zinc-300" x-text="activeSetoran ? activeSetoran.petugas : ''"></span> • Tanggal Setor: <span x-text="activeSetoran ? activeSetoran.tanggal_setor : ''"></span>
                            <template x-if="activeSetoran && activeSetoran.route_code">
                                <span> • Rute: <span class="font-mono font-semibold text-teal-600 dark:text-teal-400" x-text="activeSetoran.route_code"></span></span>
                            </template>
                        </p>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-6 overflow-y-auto space-y-4">
                    <template x-if="activeSetoran && activeSetoran.weighings && activeSetoran.weighings.length > 0">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs text-zinc-500 font-semibold uppercase tracking-wider">
                                <span>Daftar Timbangan Sampah Warga (<span x-text="activeSetoran.weighings.length"></span> Titik):</span>
                                <span class="text-teal-700 dark:text-teal-400 font-bold">Total Muatan Truk: <span x-text="activeSetoran.berat_kg"></span> kg</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="(item, idx) in activeSetoran.weighings" :key="item.id">
                                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-3 bg-zinc-50/50 dark:bg-zinc-800/40 text-xs flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm" x-text="item.warga_name"></span>
                                                <span class="font-mono font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-[11px]" x-text="item.berat_kg + ' kg'"></span>
                                            </div>
                                            <div class="text-zinc-500 flex items-center gap-2 mb-2">
                                                <span class="capitalize" x-text="item.jenis_sampah.replace('_', ' ')"></span>
                                                <span>•</span>
                                                <span class="font-semibold text-amber-600 dark:text-amber-400" x-text="'+' + item.poin_didapat + ' Poin'"></span>
                                            </div>
                                            <template x-if="item.catatan">
                                                <p class="text-[11px] text-zinc-600 dark:text-zinc-400 italic mb-2 bg-white dark:bg-zinc-800 p-1.5 rounded border border-zinc-100 dark:border-zinc-700" x-text="'Catatan: ' + item.catatan"></p>
                                            </template>
                                        </div>

                                        <div class="pt-2 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between">
                                            <span class="text-[11px] text-zinc-500">Bukti Timbangan:</span>
                                            <template x-if="item.foto_bukti">
                                                <button type="button" @click="openPhoto(item.foto_bukti, 'Timbangan ' + item.warga_name + ' (' + item.berat_kg + ' kg)')" class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-700 dark:text-teal-400 hover:underline">
                                                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span>Lihat Gambar Timbangan</span>
                                                </button>
                                            </template>
                                            <template x-if="!item.foto_bukti">
                                                <span class="text-[11px] text-zinc-400 italic">Tanpa foto timbangan</span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeSetoran && (!activeSetoran.weighings || activeSetoran.weighings.length === 0)">
                        <div class="py-8 text-center text-zinc-500">
                            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400"><flux:icon.truck class="size-6" /></div>
                            <p class="font-semibold text-sm text-zinc-800 dark:text-zinc-200">Setoran Skala Komersial / Truk Langsung</p>
                            <p class="text-xs text-zinc-400 max-w-md mx-auto mt-1">Setoran ini masuk melalui ritasi teragregasi langsung dari armada kelurahan atau setoran mandiri tanpa rincian penimbangan individual per rumah tangga.</p>
                            <div class="mt-4 inline-block bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 rounded-xl px-4 py-2 text-xs text-teal-800 dark:text-teal-300 font-bold">
                                Tonase Masuk: <span x-text="activeSetoran ? activeSetoran.berat_kg : 0"></span> kg (<span x-text="activeSetoran ? activeSetoran.jenis_sampah : ''"></span>)
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer Modal -->
                <div class="px-6 py-3 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/50">
                    <a :href="'/dashboard/tps3r/deposits/' + (activeSetoran ? activeSetoran.id : '')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-700 text-white text-xs font-bold hover:bg-teal-800 transition shadow-sm">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Buka Halaman Lengkap</span>
                    </a>
                    <button type="button" @click="detailModalOpen = false" class="px-4 py-2 rounded-xl bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-semibold hover:bg-zinc-300 transition">
                        Tutup Rincian
                    </button>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal Preview Foto Timbangan -->
        <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" @keydown.escape.window="previewModalOpen = false">
            <div class="relative max-w-2xl w-full bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700" @click.outside="previewModalOpen = false">
                <div class="px-5 py-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/80">
                    <h4 class="text-xs font-bold text-zinc-800 dark:text-zinc-100 flex items-center gap-1.5" x-text="previewPhotoTitle"></h4>
                    <button type="button" @click="previewModalOpen = false" class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 flex items-center justify-center bg-zinc-950 max-h-[70vh]">
                    <img :src="previewPhotoUrl" :alt="previewPhotoTitle" class="max-h-[65vh] w-auto object-contain rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
