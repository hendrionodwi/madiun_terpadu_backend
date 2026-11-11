<x-layouts::app :title="__('Rekap Volume Sampah per Kategori')">
    @php
        $fieldClass = 'min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div
        x-data="{
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
        }"
        class="civic-page mx-auto w-full max-w-7xl"
    >
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Rekap Volume"
            :eyebrow="($profile->kode_tps3r ?? 'TPS3R') . ' · Analitik & Neraca Sampah Fasilitas'"
            title="Rekap Volume Sampah per Kategori"
            :description="'Monitoring agregat tonase masuk, komposisi jenis sampah, dan utilisasi kapasitas di ' . ($profile->nama_tps3r ?? 'Fasilitas TPS3R') . '.'"
            icon="chart-bar"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'print'])) }}" target="_blank" class="civic-button-secondary !min-h-10 !py-2 !text-xs" title="Cetak Berita Acara Resmi Format A4">
                    <flux:icon.printer class="size-4" />
                    Cetak Berita Acara
                </a>
                <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="civic-button-secondary !min-h-10 !py-2 !text-xs" title="Unduh Tabular CSV / Excel Neraca Sampah">
                    <flux:icon.arrow-down-tray class="size-4" />
                    Export CSV
                </a>
                <a href="{{ route('dashboard.tps3r.deposits') }}" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                    <flux:icon.plus class="size-4" />
                    Input Setoran
                </a>
                <a href="{{ route('dashboard.tps3r.processing.index') }}" class="civic-button-primary !min-h-10 !py-2 !text-xs">
                    <flux:icon.arrow-path-rounded-square class="size-4" />
                    Proses Pengolahan
                </a>
            </x-slot:actions>
        </x-civic.subpage-header>

        <!-- Filter Periode & Parameter -->
        <section class="civic-card p-5">
            <form id="volumeRecapFilterForm" method="GET" action="{{ route('dashboard.tps3r.volume-recap') }}" data-ajax-form data-ajax-targets="#volumeRecapQuickBar,#volumeRecapResults" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Dari Tanggal</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Sampai Tanggal</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="{{ $fieldClass }}">
                </label>

                <div class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Jenis Sampah</span>
                    <x-public.select-filter
                        name="jenis_sampah"
                        :value="$selectedJenis"
                        placeholder="Semua Kategori"
                        heightClass="h-[2.5rem] text-sm"
                        :options="[
                            'organik' => 'Organik',
                            'anorganik_daur_ulang' => 'Anorganik Daur Ulang',
                            'residu' => 'Residu',
                        ]"
                    />
                </div>

                <div class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Sumber Sampah</span>
                    <x-public.select-filter
                        name="sumber_sampah"
                        :value="$selectedSumber"
                        placeholder="Semua Sumber"
                        heightClass="h-[2.5rem] text-sm"
                        :options="[
                            'armada_kelurahan' => 'Armada Kelurahan',
                            'warga_mandiri' => 'Warga Mandiri',
                            'pemrakarsa_komersial' => 'Pemrakarsa / Komersial',
                        ]"
                    />
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="civic-button-primary flex-1 !min-h-10 !py-2 !text-xs">
                        Filter Data
                    </button>
                    <a href="{{ route('dashboard.tps3r.volume-recap') }}" data-ajax-link data-ajax-targets="#volumeRecapQuickBar,#volumeRecapResults" class="civic-button-secondary !min-h-10 !py-2 !text-xs" title="Reset filter ke bulan berjalan">
                        Reset
                    </a>
                </div>
            </form>

            <div id="volumeRecapQuickBar" data-ajax-targets="#volumeRecapQuickBar,#volumeRecapResults" class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500">Preset Cepat:</span>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]) }}" data-ajax-link class="civic-filter-chip bg-slate-100 !text-xs dark:bg-slate-800">
                        Hari Ini
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString()]) }}" data-ajax-link class="civic-filter-chip bg-slate-100 !text-xs dark:bg-slate-800">
                        7 Hari Terakhir
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" data-ajax-link class="civic-filter-chip bg-slate-100 !text-xs dark:bg-slate-800">
                        Bulan Berjalan
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap', ['start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->toDateString()]) }}" data-ajax-link class="civic-filter-chip bg-slate-100 !text-xs dark:bg-slate-800">
                        Tahun Ini
                    </a>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'print'])) }}" target="_blank" class="civic-button-secondary !min-h-8 !px-3 !py-1 !text-xs">
                        <flux:icon.printer class="size-3.5" />
                        Cetak Laporan Sesuai Filter
                    </a>
                    <a href="{{ route('dashboard.tps3r.volume-recap.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="civic-button-secondary !min-h-8 !px-3 !py-1 !text-xs">
                        <flux:icon.arrow-down-tray class="size-3.5" />
                        Unduh CSV Sesuai Filter
                    </a>
                </div>
            </div>
        </section>

        <div id="volumeRecapResults" data-ajax-zone="tps3r-volume-recap" data-ajax-targets="#volumeRecapQuickBar,#volumeRecapResults" class="space-y-6">
            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="civic-stat-card">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Volume Masuk</span>
                        <span class="civic-icon-tile !size-8"><flux:icon.scale class="size-4" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="font-display text-2xl font-extrabold text-civic-700 dark:text-civic-300">
                            {{ number_format($totalVolumeKg, 1, ',', '.') }} <span class="text-sm font-semibold text-slate-500">kg</span>
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Setara <strong>{{ number_format($totalVolumeTon, 2, ',', '.') }} Ton</strong> ({{ $totalSetoranCount }} rit/setoran)
                        </p>
                    </div>
                </div>

                <div class="civic-stat-card border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Sampah Organik</span>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300"><flux:icon.sparkles class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="font-display text-2xl font-extrabold text-emerald-700 dark:text-emerald-400">
                            {{ number_format($organikKg, 1, ',', '.') }} <span class="text-sm font-semibold text-slate-500">kg</span>
                        </h3>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="rounded bg-emerald-200/70 px-1.5 py-0.5 text-xs font-bold text-emerald-900 dark:bg-emerald-900/70 dark:text-emerald-200">{{ $organikPct }}%</span>
                            <span class="text-xs text-slate-500">Kompos & Pakan Maggot</span>
                        </div>
                    </div>
                </div>

                <div class="civic-stat-card border-sky-200 bg-sky-50/50 dark:border-sky-900/50 dark:bg-sky-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Anorganik Daur Ulang</span>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-300"><flux:icon.arrow-path-rounded-square class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="font-display text-2xl font-extrabold text-sky-700 dark:text-sky-400">
                            {{ number_format($anorganikKg, 1, ',', '.') }} <span class="text-sm font-semibold text-slate-500">kg</span>
                        </h3>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="rounded bg-sky-200/70 px-1.5 py-0.5 text-xs font-bold text-sky-900 dark:bg-sky-900/70 dark:text-sky-200">{{ $anorganikPct }}%</span>
                            <span class="text-xs text-slate-500">Bank Sampah & Marketplace</span>
                        </div>
                    </div>
                </div>

                <div class="civic-stat-card border-red-200 bg-red-50/50 dark:border-red-900/50 dark:bg-red-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-red-800 dark:text-red-300">Residu (TPA)</span>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300"><flux:icon.trash class="size-5" /></span>
                    </div>
                    <div class="mt-3">
                        <h3 class="font-display text-2xl font-extrabold text-red-700 dark:text-red-400">
                            {{ number_format($residuKg, 1, ',', '.') }} <span class="text-sm font-semibold text-slate-500">kg</span>
                        </h3>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="rounded bg-red-200/70 px-1.5 py-0.5 text-xs font-bold text-red-900 dark:bg-red-900/70 dark:text-red-200">{{ $residuPct }}%</span>
                            <span class="text-xs text-slate-500">Pengangkutan ke TPA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Komposisi & Utilisasi Kapasitas -->
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="civic-card p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                            Proporsi Neraca Sampah
                        </h2>
                        <span class="text-xs text-slate-500">Target Diversi Sampah Kota</span>
                    </div>

                    <div class="mt-5">
                        <div class="flex h-5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            @if ($totalVolumeKg > 0)
                                <div style="width: {{ $organikPct }}%" class="bg-emerald-500 transition-all duration-500" title="Organik: {{ $organikPct }}%"></div>
                                <div style="width: {{ $anorganikPct }}%" class="bg-sky-500 transition-all duration-500" title="Anorganik: {{ $anorganikPct }}%"></div>
                                <div style="width: {{ $residuPct }}%" class="bg-red-500 transition-all duration-500" title="Residu: {{ $residuPct }}%"></div>
                            @else
                                <div class="w-full bg-slate-200 text-center text-xs text-slate-500 dark:bg-slate-800">Belum ada data</div>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-2.5 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                                <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                    <span class="size-2 rounded-full bg-emerald-500"></span> Organik
                                </div>
                                <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">{{ $organikPct }}%</p>
                                <p class="text-xs text-slate-500">{{ number_format($organikKg, 0, ',', '.') }} kg</p>
                            </div>

                            <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-2.5 dark:border-sky-900/40 dark:bg-sky-950/20">
                                <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-sky-800 dark:text-sky-300">
                                    <span class="size-2 rounded-full bg-sky-500"></span> Anorganik
                                </div>
                                <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">{{ $anorganikPct }}%</p>
                                <p class="text-xs text-slate-500">{{ number_format($anorganikKg, 0, ',', '.') }} kg</p>
                            </div>

                            <div class="rounded-xl border border-red-100 bg-red-50/50 p-2.5 dark:border-red-900/40 dark:bg-red-950/20">
                                <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-red-800 dark:text-red-300">
                                    <span class="size-2 rounded-full bg-red-500"></span> Residu
                                </div>
                                <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">{{ $residuPct }}%</p>
                                <p class="text-xs text-slate-500">{{ number_format($residuKg, 0, ',', '.') }} kg</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-start gap-2 rounded-xl border border-civic-100 bg-civic-50/70 p-3 text-xs text-slate-700 dark:border-civic-900 dark:bg-civic-950/30 dark:text-slate-300">
                        <flux:icon.light-bulb class="mt-0.5 size-4 shrink-0 text-civic-700 dark:text-civic-300" />
                        <span><strong>Tingkat Reduksi Sampah:</strong> Sebanyak <strong>{{ round($organikPct + $anorganikPct, 1) }}%</strong> sampah berhasil dialihkan dari Tempat Pemrosesan Akhir (TPA) untuk diolah kembali di TPS3R.</span>
                    </div>
                </section>

                <section class="civic-card p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                            Kapasitas & Beban Fasilitas
                        </h2>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            Kapasitas: {{ number_format($kapasitasHarian, 0, ',', '.') }} kg/hari
                        </span>
                    </div>

                    <div class="mt-5">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-400">
                            <span>Rata-rata Masuk: {{ number_format($rataRataHarianKg, 1, ',', '.') }} kg/hari</span>
                            <span class="font-bold text-civic-700 dark:text-civic-300">{{ $persenUtilisasi }}% Kapasitas</span>
                        </div>

                        <div class="mt-2 h-4 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div style="width: {{ min(100, $persenUtilisasi) }}%" class="h-full rounded-full transition-all duration-500 {{ $persenUtilisasi > 90 ? 'bg-amber-500' : 'bg-civic-700' }}"></div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <span class="text-xs text-slate-500">Daya Tampung Tersisa:</span>
                                <p class="mt-0.5 font-bold text-slate-950 dark:text-white">
                                    {{ number_format(max(0, $kapasitasHarian - $rataRataHarianKg), 1, ',', '.') }} kg / hari
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <span class="text-xs text-slate-500">Status Operasional:</span>
                                <p class="mt-0.5 font-bold {{ $persenUtilisasi > 90 ? 'text-amber-600' : 'text-civic-700 dark:text-civic-300' }}">
                                    {{ $persenUtilisasi > 90 ? 'Mendekati Batas' : 'Kapasitas Prima' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-500">Kontribusi per Sumber:</span>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($breakdownSumber as $sumber)
                                @php
                                    $pctSumber = $totalVolumeKg > 0 ? round(($sumber->total_kg / $totalVolumeKg) * 100, 1) : 0;
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    <span class="font-semibold capitalize">{{ str_replace('_', ' ', $sumber->sumber_sampah) }}:</span>
                                    <span>{{ number_format($sumber->total_kg, 1, ',', '.') }} kg ({{ $pctSumber }}%)</span>
                                </span>
                            @empty
                                <span class="text-xs text-slate-400">Belum ada distribusi sumber tercatat.</span>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <!-- Rekap Harian 14 Hari Terakhir -->
            @if ($dailyRecords->isNotEmpty())
                <section class="civic-card overflow-hidden">
                    <div class="civic-section-header">
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                            Tren Agregat Harian (14 Hari Terakhir dalam Periode)
                        </h2>
                        <span class="text-xs text-slate-500">Beban per tanggal setor</span>
                    </div>

                    <div class="civic-table-wrap rounded-none border-0">
                        <table class="civic-table">
                            <thead>
                                <tr>
                                    <th>Tanggal Setor</th>
                                    <th class="text-center">Rit / Ritasi Masuk</th>
                                    <th class="text-right">Total Berat (kg)</th>
                                    <th class="text-right">Beban Kapasitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dailyRecords as $record)
                                    @php
                                        $dayPct = $kapasitasHarian > 0 ? min(100, round(($record->total_kg / $kapasitasHarian) * 100, 1)) : 0;
                                    @endphp
                                    <tr>
                                        <td class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ \Carbon\Carbon::parse($record->tanggal_setor)->translatedFormat('l, d M Y') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $record->total_rit }} rit
                                            </span>
                                        </td>
                                        <td class="text-right font-bold text-civic-700 dark:text-civic-300">
                                            {{ number_format($record->total_kg, 1, ',', '.') }} kg
                                        </td>
                                        <td class="text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <span class="text-xs font-semibold {{ $dayPct > 90 ? 'text-amber-600' : 'text-civic-700 dark:text-civic-300' }}">
                                                    {{ $dayPct }}%
                                                </span>
                                                <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                    <div style="width: {{ $dayPct }}%" class="h-full {{ $dayPct > 90 ? 'bg-amber-500' : 'bg-civic-600' }}"></div>
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
            <section class="civic-card overflow-hidden">
                <div class="civic-section-header">
                    <div>
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                            Log Riwayat Setoran Sampah
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Rincian resi dan timbangan setoran yang terdata pada periode filter.</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">
                        Menampilkan {{ $riwayatSetoran->total() }} entri
                    </span>
                </div>

                <div class="civic-table-wrap rounded-none border-0">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nomor Resi</th>
                                <th>Sumber Sampah</th>
                                <th>Kategori</th>
                                <th class="text-right">Berat (kg)</th>
                                <th>Penerima/Petugas</th>
                                <th class="text-center">Muatan Warga</th>
                                <th>Catatan</th>
                                <th class="text-center">Aksi Laporan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayatSetoran as $setoran)
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
                                        'weighings' => $setoran->weighings->map(fn ($w) => [
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
                                    $badgeClass = match ($setoran->jenis_sampah) {
                                        'organik' => 'civic-status-success',
                                        'anorganik_daur_ulang' => 'civic-status-info',
                                        default => 'civic-status-danger',
                                    };
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                                        {{ $setoran->tanggal_setor->format('d/m/Y') }}
                                    </td>
                                    <td class="font-mono text-xs font-bold text-civic-700 dark:text-civic-300">
                                        {{ $setoran->nomor_resi }}
                                    </td>
                                    <td class="whitespace-nowrap capitalize">
                                        {{ str_replace('_', ' ', $setoran->sumber_sampah) }}
                                    </td>
                                    <td>
                                        <span class="{{ $badgeClass }} capitalize">
                                            {{ str_replace('_', ' ', $setoran->jenis_sampah) }}
                                        </span>
                                    </td>
                                    <td class="text-right font-bold text-slate-950 dark:text-white">
                                        {{ number_format($setoran->berat_kg, 1, ',', '.') }} kg
                                    </td>
                                    <td class="text-xs">
                                        {{ $setoran->petugas?->name ?? $setoran->pengelola?->name ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        @if ($setoran->weighings->isNotEmpty())
                                            <button type="button" @click='openDetail(@json($setoranPayload))' class="civic-button-secondary !min-h-8 !px-2.5 !py-1 !text-xs">
                                                <flux:icon.eye class="size-3.5" />
                                                <span>{{ $setoran->weighings->count() }} Warga</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">Direct Truk</span>
                                        @endif
                                    </td>
                                    <td class="text-xs text-slate-500">
                                        {{ $setoran->catatan ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        <a href="{{ route('dashboard.tps3r.deposits.show', $setoran) }}" class="civic-button-secondary !min-h-8 !px-2.5 !py-1 !text-xs">
                                            <flux:icon.document-text class="size-3.5" />
                                            <span>Detail Sampah</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Tidak ada data setoran sampah pada rentang filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($riwayatSetoran->hasPages())
                    <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                        {{ $riwayatSetoran->links() }}
                    </div>
                @endif
            </section>
        </div>

        <!-- Modal Detail Laporan Sampah -->
        <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" @keydown.escape.window="detailModalOpen = false">
            <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900" @click.outside="detailModalOpen = false">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/50">
                    <div>
                        <h3 class="flex items-center gap-2 font-display text-base font-extrabold text-slate-950 dark:text-white">
                            <span>Detail Laporan Sampah Warga</span>
                            <span class="rounded bg-civic-100 px-2 py-0.5 font-mono text-xs font-semibold text-civic-800 dark:bg-civic-950 dark:text-civic-300" x-text="activeSetoran ? activeSetoran.nomor_resi : ''"></span>
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Penyetor/Petugas: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="activeSetoran ? activeSetoran.petugas : ''"></span> • Tanggal Setor: <span x-text="activeSetoran ? activeSetoran.tanggal_setor : ''"></span>
                            <template x-if="activeSetoran && activeSetoran.route_code">
                                <span> • Rute: <span class="font-mono font-semibold text-civic-700 dark:text-civic-300" x-text="activeSetoran.route_code"></span></span>
                            </template>
                        </p>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto p-6">
                    <template x-if="activeSetoran && activeSetoran.weighings && activeSetoran.weighings.length > 0">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <span>Daftar Timbangan Sampah Warga (<span x-text="activeSetoran.weighings.length"></span> Titik):</span>
                                <span class="font-bold text-civic-700 dark:text-civic-300">Total Muatan Truk: <span x-text="activeSetoran.berat_kg"></span> kg</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <template x-for="(item, idx) in activeSetoran.weighings" :key="item.id">
                                    <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs dark:border-slate-700 dark:bg-slate-800/40">
                                        <div>
                                            <div class="mb-1 flex items-center justify-between">
                                                <span class="text-sm font-bold text-slate-950 dark:text-white" x-text="item.warga_name"></span>
                                                <span class="rounded bg-civic-100 px-2 py-0.5 font-mono text-xs font-semibold text-civic-800 dark:bg-civic-950 dark:text-civic-300" x-text="item.berat_kg + ' kg'"></span>
                                            </div>
                                            <div class="mb-2 flex items-center gap-2 text-slate-500">
                                                <span class="capitalize" x-text="item.jenis_sampah.replace('_', ' ')"></span>
                                                <span>•</span>
                                                <span class="font-semibold text-amber-600 dark:text-amber-400" x-text="'+' + item.poin_didapat + ' Poin'"></span>
                                            </div>
                                            <template x-if="item.catatan">
                                                <p class="mb-2 rounded border border-slate-100 bg-white p-1.5 text-xs italic text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400" x-text="'Catatan: ' + item.catatan"></p>
                                            </template>
                                        </div>

                                        <div class="flex items-center justify-between border-t border-slate-200/60 pt-2 dark:border-slate-700/60">
                                            <span class="text-xs text-slate-500">Bukti Timbangan:</span>
                                            <template x-if="item.foto_bukti">
                                                <button type="button" @click="openPhoto(item.foto_bukti, 'Timbangan ' + item.warga_name + ' (' + item.berat_kg + ' kg)')" class="civic-link">
                                                    <flux:icon.photo class="size-3.5" />
                                                    <span>Lihat Gambar Timbangan</span>
                                                </button>
                                            </template>
                                            <template x-if="!item.foto_bukti">
                                                <span class="text-xs italic text-slate-400">Tanpa foto timbangan</span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeSetoran && (!activeSetoran.weighings || activeSetoran.weighings.length === 0)">
                        <div class="py-8 text-center text-slate-500">
                            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><flux:icon.truck class="size-6" /></div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Setoran Skala Komersial / Truk Langsung</p>
                            <p class="mx-auto mt-1 max-w-md text-xs text-slate-400">Setoran ini masuk melalui ritasi teragregasi langsung dari armada kelurahan atau setoran mandiri tanpa rincian penimbangan individual per rumah tangga.</p>
                            <div class="mt-4 inline-block rounded-xl border border-civic-200 bg-civic-50 px-4 py-2 text-xs font-bold text-civic-800 dark:border-civic-800 dark:bg-civic-950/50 dark:text-civic-300">
                                Tonase Masuk: <span x-text="activeSetoran ? activeSetoran.berat_kg : 0"></span> kg (<span x-text="activeSetoran ? activeSetoran.jenis_sampah : ''"></span>)
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-3 dark:border-slate-800 dark:bg-slate-800/50">
                    <a :href="'/dashboard/tps3r/deposits/' + (activeSetoran ? activeSetoran.id : '')" class="civic-button-primary !min-h-9 !px-4 !py-1.5 !text-xs">
                        <span>Buka Halaman Lengkap</span>
                    </a>
                    <button type="button" @click="detailModalOpen = false" class="civic-button-secondary !min-h-9 !px-4 !py-1.5 !text-xs">
                        Tutup Rincian
                    </button>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal Preview Foto Timbangan -->
        <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-md" @keydown.escape.window="previewModalOpen = false">
            <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl" @click.outside="previewModalOpen = false">
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-950 px-5 py-3">
                    <h4 class="flex items-center gap-1.5 text-xs font-bold text-slate-100" x-text="previewPhotoTitle"></h4>
                    <button type="button" @click="previewModalOpen = false" class="rounded p-1 text-slate-400 hover:text-slate-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex max-h-[70vh] items-center justify-center bg-slate-950 p-4">
                    <img :src="previewPhotoUrl" :alt="previewPhotoTitle" class="max-h-[65vh] w-auto rounded-lg object-contain shadow-lg">
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
