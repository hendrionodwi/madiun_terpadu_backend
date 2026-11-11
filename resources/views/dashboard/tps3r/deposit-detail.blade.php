<x-layouts::app :title="__('Detail Laporan Setoran Sampah - ' . $deposit->nomor_resi)">
    @php
        $fieldClass = 'min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div
        x-data="{
            previewModalOpen: false,
            previewPhotoUrl: null,
            previewPhotoTitle: '',
            openPhotoPreview(url, title) {
                this.previewPhotoUrl = url;
                this.previewPhotoTitle = title || 'Bukti Penimbangan Sampah';
                this.previewModalOpen = true;
            }
        }"
        class="civic-page mx-auto w-full max-w-7xl print:p-0"
    >
        <!-- Navigation Breadcrumbs & Actions -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.tps3r.volume-recap') }}" class="flex items-center gap-1 font-semibold transition hover:text-civic-700 dark:hover:text-civic-300">
                    <flux:icon.arrow-left class="size-3.5" />
                    <span>Rekap Volume TPS3R</span>
                </a>
                <span>/</span>
                <a href="{{ route('dashboard.tps3r.deposits') }}" class="font-semibold transition hover:text-civic-700 dark:hover:text-civic-300">
                    Setoran Sampah
                </a>
                <span>/</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $deposit->nomor_resi }}</span>
            </nav>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                    <flux:icon.printer class="size-4" />
                    <span>Cetak Manifest</span>
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="civic-alert-success print:hidden" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Page Header Card -->
        <div class="civic-card p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="font-display text-2xl font-extrabold text-slate-950 dark:text-white">
                            Manifest Setoran Sampah
                        </h1>
                        <span class="rounded-lg bg-civic-100 px-2.5 py-1 font-mono text-sm font-bold text-civic-800 dark:bg-civic-950 dark:text-civic-300">
                            {{ $deposit->nomor_resi }}
                        </span>
                        @if ($deposit->status === 'diterima')
                            <span class="civic-status-success">
                                Diterima & Terverifikasi
                            </span>
                        @elseif ($deposit->status === 'menunggu_konfirmasi')
                            <span class="civic-status-warning">
                                Menunggu Verifikasi TPS3R
                            </span>
                        @elseif ($deposit->status === 'perlu_perbaikan')
                            <span class="civic-status-warning">
                                Perlu Perbaikan
                            </span>
                        @else
                            <span class="civic-status-danger">
                                Ditolak
                            </span>
                        @endif
                    </div>
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                        Dokumen manifest logistik persampahan terintegrasi DLH Kota Madiun & TPS3R Kelurahan.
                    </p>
                </div>

                <div class="flex items-center gap-4 text-right">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-slate-400">Total Berat Setoran</div>
                        <div class="font-display text-2xl font-extrabold text-civic-700 dark:text-civic-300">
                            {{ number_format($deposit->berat_konfirmasi_kg ?? $deposit->berat_kg, 1, ',', '.') }} <span class="text-sm font-semibold text-slate-500">kg</span>
                        </div>
                        @if ($deposit->berat_konfirmasi_kg && $deposit->berat_konfirmasi_kg != $deposit->berat_kg)
                            <div class="text-xs text-slate-400 line-through">
                                Input armada: {{ number_format($deposit->berat_kg, 1, ',', '.') }} kg
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Meta Details Grid -->
            <div class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5 text-xs sm:grid-cols-4 dark:border-slate-800">
                <div>
                    <span class="text-slate-400">Tanggal & Waktu Setor</span>
                    <p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">
                        {{ $deposit->tanggal_setor->format('d M Y') }}
                        <span class="font-normal text-slate-400">({{ $deposit->created_at->format('H:i') }} WIB)</span>
                    </p>
                </div>
                <div>
                    <span class="text-slate-400">Petugas Armada / Sopir</span>
                    <p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">
                        {{ $deposit->petugas?->name ?? 'Mandiri / Non-Armada' }}
                        @if ($deposit->petugas?->petugasLapanganProfile?->nomor_kendaraan)
                            <span class="block font-mono text-civic-700 dark:text-civic-300">
                                {{ $deposit->petugas->petugasLapanganProfile->nomor_kendaraan }}
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-slate-400">Sumber & Rute Wilayah</span>
                    <p class="mt-0.5 font-semibold capitalize text-slate-900 dark:text-slate-100">
                        {{ str_replace('_', ' ', $deposit->sumber_sampah) }}
                        @if ($deposit->route_code)
                            <span class="ml-1 inline-block rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                Rute: {{ $deposit->route_code }}
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-slate-400">Fasilitas TPS3R Tujuan</span>
                    <p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">
                        {{ $deposit->tps3rProfile?->nama_tps3r ?? 'TPS3R Kota Madiun' }}
                        <span class="block font-normal text-slate-500">
                            Kel. {{ $deposit->tps3rProfile?->kelurahan ?? 'Kota Madiun' }}
                        </span>
                    </p>
                </div>
            </div>

            @if ($deposit->catatan || $deposit->catatan_verifikasi)
                <div class="mt-4 flex flex-col gap-1.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                    @if ($deposit->catatan)
                        <div><strong class="text-slate-800 dark:text-slate-200">Catatan Pengirim:</strong> {{ $deposit->catatan }}</div>
                    @endif
                    @if ($deposit->catatan_verifikasi)
                        <div><strong class="text-civic-700 dark:text-civic-300">Catatan Verifikasi TPS3R:</strong> {{ $deposit->catatan_verifikasi }} (diverifikasi {{ $deposit->verified_at?->format('d M Y H:i') ?? '-' }})</div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Neraca Komposisi 3 Pilar (Organik, Anorganik Daur Ulang, Residu) -->
        <section class="civic-card p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="flex items-center gap-2 font-display text-base font-extrabold text-slate-950 dark:text-white">
                        <span>Neraca & Komposisi Muatan Truk</span>
                        <span class="text-xs font-normal text-slate-500">(Standarisasi 3 Pilar DLH Kota Madiun)</span>
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        @if ($deposit->berat_organik_pilah_kg !== null || $deposit->berat_anorganik_pilah_kg !== null || $deposit->berat_residu_pilah_kg !== null)
                            <span class="font-semibold text-civic-700 dark:text-civic-300">✓ Data bersumber dari hasil Pemilahan Lanjutan di Fasilitas TPS3R.</span>
                        @elseif ($deposit->weighings->isNotEmpty())
                            <span class="font-medium text-civic-700 dark:text-civic-300">Data dihitung dari akumulasi {{ $deposit->weighings->count() }} timbangan warga di sepanjang rute penjemputan.</span>
                        @else
                            <span class="text-slate-500">Kategori tunggal dicatat langsung oleh armada truk.</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Segmented Progress Bar -->
            <div class="mt-5 flex h-4 w-full overflow-hidden rounded-full bg-slate-100 shadow-inner dark:bg-slate-800">
                @if ($composition['organik']['percentage'] > 0)
                    <div style="width: {{ $composition['organik']['percentage'] }}%" class="h-full bg-emerald-500 transition-all" title="Organik: {{ $composition['organik']['percentage'] }}%"></div>
                @endif
                @if ($composition['anorganik_daur_ulang']['percentage'] > 0)
                    <div style="width: {{ $composition['anorganik_daur_ulang']['percentage'] }}%" class="h-full bg-sky-500 transition-all" title="Anorganik Daur Ulang: {{ $composition['anorganik_daur_ulang']['percentage'] }}%"></div>
                @endif
                @if ($composition['residu']['percentage'] > 0)
                    <div style="width: {{ $composition['residu']['percentage'] }}%" class="h-full bg-red-500 transition-all" title="Residu TPA: {{ $composition['residu']['percentage'] }}%"></div>
                @endif
            </div>

            <!-- Composition Metric Cards -->
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300"><flux:icon.sparkles class="size-4" /></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Organik</span>
                        </div>
                        <span class="text-xs font-extrabold text-emerald-700 dark:text-emerald-300">{{ $composition['organik']['percentage'] }}%</span>
                    </div>
                    <div class="mt-3">
                        <div class="font-display text-xl font-extrabold text-emerald-800 dark:text-emerald-300">
                            {{ number_format($composition['organik']['weight'], 1, ',', '.') }} <span class="text-xs font-semibold text-slate-500">kg</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Diproses untuk komposting & pakan maggot BSF</p>
                    </div>
                </div>

                <div class="rounded-xl border border-sky-200 bg-sky-50/60 p-4 dark:border-sky-900/40 dark:bg-sky-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-300"><flux:icon.arrow-path-rounded-square class="size-4" /></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Anorganik Daur Ulang</span>
                        </div>
                        <span class="text-xs font-extrabold text-sky-700 dark:text-sky-300">{{ $composition['anorganik_daur_ulang']['percentage'] }}%</span>
                    </div>
                    <div class="mt-3">
                        <div class="font-display text-xl font-extrabold text-sky-800 dark:text-sky-300">
                            {{ number_format($composition['anorganik_daur_ulang']['weight'], 1, ',', '.') }} <span class="text-xs font-semibold text-slate-500">kg</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Plastik, kertas, botol kaca, logam & kardus bernilai ekonomi</p>
                    </div>
                </div>

                <div class="rounded-xl border border-red-200 bg-red-50/60 p-4 dark:border-red-900/40 dark:bg-red-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300"><flux:icon.trash class="size-4" /></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-red-800 dark:text-red-300">Residu (TPA)</span>
                        </div>
                        <span class="text-xs font-extrabold text-red-700 dark:text-red-300">{{ $composition['residu']['percentage'] }}%</span>
                    </div>
                    <div class="mt-3">
                        <div class="font-display text-xl font-extrabold text-red-800 dark:text-red-300">
                            {{ number_format($composition['residu']['weight'], 1, ',', '.') }} <span class="text-xs font-semibold text-slate-500">kg</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Sampah tidak dapat diolah, ditransfer ke TPA Winongo</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Form Pemilahan Lanjutan di TPS3R -->
        <section class="civic-card p-6 print:hidden">
            <div>
                <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                    Verifikasi & Koreksi Pemilahan Lanjutan TPS3R
                </h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    Pengelola TPS3R dapat mencatat berat aktual hasil pemilahan material recovery (organik, anorganik terpilah, dan residu) saat memverifikasi muatan truk.
                </p>
            </div>

            <form method="POST" action="{{ route('dashboard.tps3r.verify-deposit', $deposit) }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                @method('PATCH')

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Status Verifikasi Setoran</span>
                    <select name="status" required class="{{ $fieldClass }}">
                        <option value="diterima" @selected($deposit->status === 'diterima' || $deposit->status === 'menunggu_konfirmasi')>Diterima & Selesai Timbang</option>
                        <option value="perlu_perbaikan" @selected($deposit->status === 'perlu_perbaikan')>Minta Perbaikan Data</option>
                        <option value="ditolak" @selected($deposit->status === 'ditolak')>Tolak Setoran</option>
                    </select>
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Total Berat Konfirmasi (kg)</span>
                    <input
                        type="number"
                        step="0.1"
                        min="0.1"
                        name="berat_konfirmasi_kg"
                        value="{{ old('berat_konfirmasi_kg', $deposit->berat_konfirmasi_kg ?? $deposit->berat_kg) }}"
                        required
                        class="{{ $fieldClass }}"
                    >
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-emerald-800 dark:text-emerald-300">
                    <span class="inline-flex items-center gap-1.5"><flux:icon.sparkles class="size-4" /> Organik Terpilah (kg)</span>
                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        name="berat_organik_pilah_kg"
                        value="{{ old('berat_organik_pilah_kg', $deposit->berat_organik_pilah_kg ?? ($composition['organik']['weight'] > 0 ? $composition['organik']['weight'] : null)) }}"
                        placeholder="Contoh: 35.5"
                        class="min-h-10 w-full rounded-xl border border-emerald-300 bg-white px-3 py-2 text-xs text-slate-900 dark:border-emerald-700 dark:bg-slate-950 dark:text-slate-100"
                    >
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-sky-800 dark:text-sky-300">
                    <span class="inline-flex items-center gap-1.5"><flux:icon.arrow-path-rounded-square class="size-4" /> Anorganik Daur Ulang Terpilah (kg)</span>
                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        name="berat_anorganik_pilah_kg"
                        value="{{ old('berat_anorganik_pilah_kg', $deposit->berat_anorganik_pilah_kg ?? ($composition['anorganik_daur_ulang']['weight'] > 0 ? $composition['anorganik_daur_ulang']['weight'] : null)) }}"
                        placeholder="Contoh: 25.0"
                        class="min-h-10 w-full rounded-xl border border-sky-300 bg-white px-3 py-2 text-xs text-slate-900 dark:border-sky-700 dark:bg-slate-950 dark:text-slate-100"
                    >
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-red-800 dark:text-red-300">
                    <span class="inline-flex items-center gap-1.5"><flux:icon.trash class="size-4" /> Residu TPA Terpilah (kg)</span>
                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        name="berat_residu_pilah_kg"
                        value="{{ old('berat_residu_pilah_kg', $deposit->berat_residu_pilah_kg ?? ($composition['residu']['weight'] > 0 ? $composition['residu']['weight'] : null)) }}"
                        placeholder="Contoh: 10.0"
                        class="min-h-10 w-full rounded-xl border border-red-300 bg-white px-3 py-2 text-xs text-slate-900 dark:border-red-700 dark:bg-slate-950 dark:text-slate-100"
                    >
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 sm:col-span-2 lg:col-span-2">
                    <span>Catatan Verifikasi / Kondisi Muatan</span>
                    <input
                        type="text"
                        maxlength="500"
                        name="catatan_verifikasi"
                        value="{{ old('catatan_verifikasi', $deposit->catatan_verifikasi) }}"
                        placeholder="Contoh: Muatan sesuai manifest rute, pemilahan organik masuk komposting."
                        class="{{ $fieldClass }}"
                    >
                </label>

                <div class="flex items-end">
                    <button type="submit" class="civic-button-primary w-full !min-h-10 !text-xs">
                        Simpan Verifikasi & Pemilahan
                    </button>
                </div>
            </form>
        </section>

        <!-- Manifest Sampah Warga -->
        <section class="civic-card overflow-hidden">
            <div class="civic-section-header">
                <div>
                    <h2 class="flex items-center gap-2 font-display text-base font-extrabold text-slate-950 dark:text-white">
                        <span>Manifest Sampah Warga Terangkut</span>
                        <span class="civic-status-success">
                            {{ $deposit->weighings->count() }} Rumah Tangga
                        </span>
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Rincian penimbangan sampah langsung dari warga penerima layanan di sepanjang rute armada ini.
                    </p>
                </div>

                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Total Poin Disalurkan: <strong class="font-bold text-civic-700 dark:text-civic-300">{{ number_format($deposit->weighings->sum('poin_didapat')) }} Poin</strong>
                </div>
            </div>

            @if ($deposit->weighings->isNotEmpty())
                <div class="civic-table-wrap rounded-none border-0">
                    <table class="civic-table text-xs">
                        <thead>
                            <tr>
                                <th class="w-10 text-center">#</th>
                                <th>Nama Warga & Kontak</th>
                                <th>Alamat / Kelurahan</th>
                                <th>Kategori Sampah</th>
                                <th class="text-right">Berat (kg)</th>
                                <th class="text-right">Poin Reward</th>
                                <th class="text-center">Foto Bukti Timbangan</th>
                                <th>Catatan Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deposit->weighings as $index => $w)
                                @php
                                    $pillar = \App\Models\MasterData::resolvePillar($w->jenis_sampah);
                                    $fotoUrl = $w->foto_bukti ? (str_starts_with($w->foto_bukti, 'http') ? $w->foto_bukti : asset('storage/' . $w->foto_bukti)) : null;
                                @endphp
                                <tr>
                                    <td class="text-center font-mono text-slate-400">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="font-bold text-slate-950 dark:text-white">{{ $w->warga?->name ?? 'Warga Kota Madiun' }}</div>
                                        <div class="font-mono text-xs text-slate-500">{{ $w->warga?->phone_number ?? '-' }}</div>
                                    </td>
                                    <td class="text-slate-600 dark:text-slate-400">
                                        {{ $w->warga?->wargaProfile?->alamat ?? 'Kel. ' . ($w->warga?->wargaProfile?->kelurahan ?? '-') }}
                                    </td>
                                    <td>
                                        <div class="font-semibold capitalize text-slate-800 dark:text-slate-200">{{ str_replace('_', ' ', $w->jenis_sampah) }}</div>
                                        <span class="mt-0.5 {{ $pillar === 'organik' ? 'civic-status-success' : ($pillar === 'anorganik_daur_ulang' ? 'civic-status-info' : 'civic-status-danger') }}">
                                            {{ str_replace('_', ' ', $pillar) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-right font-bold text-slate-950 dark:text-white">
                                        {{ number_format($w->berat_kg, 1, ',', '.') }} kg
                                    </td>
                                    <td class="whitespace-nowrap text-right font-bold text-civic-700 dark:text-civic-300">
                                        +{{ number_format($w->poin_didapat) }}
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        @if ($fotoUrl)
                                            <button
                                                type="button"
                                                @click="openPhotoPreview('{{ $fotoUrl }}', 'Bukti Timbangan {{ addslashes($w->warga?->name ?? 'Warga') }} ({{ $w->berat_kg }} kg {{ $w->jenis_sampah }})')"
                                                class="group relative inline-block overflow-hidden rounded-lg border border-slate-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-civic-500 dark:border-slate-700"
                                            >
                                                <img src="{{ $fotoUrl }}" alt="Bukti Timbangan" class="size-11 object-cover transition group-hover:scale-105" loading="lazy">
                                            </button>
                                        @else
                                            <span class="text-xs italic text-slate-400">Tanpa Foto</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs truncate italic text-slate-500">{{ $w->catatan ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/80 font-bold dark:bg-slate-800/40">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right uppercase text-slate-500">Total Akumulasi Manifest:</td>
                                <td class="px-4 py-3 text-right font-extrabold text-civic-700 dark:text-civic-300">
                                    {{ number_format($deposit->weighings->sum('berat_kg'), 1, ',', '.') }} kg
                                </td>
                                <td class="px-4 py-3 text-right font-extrabold text-civic-700 dark:text-civic-300">
                                    +{{ number_format($deposit->weighings->sum('poin_didapat')) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="civic-empty m-5">
                    Setoran ini dicatat langsung per kontainer / rit truk tanpa lampiran data timbangan rumah tangga individual.
                </div>
            @endif
        </section>

        <!-- Lightbox Modal Foto Bukti Timbangan Warga -->
        <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-md" @keydown.escape.window="previewModalOpen = false">
            <div class="relative w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl" @click.outside="previewModalOpen = false">
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-950 px-4 py-3">
                    <h4 class="text-sm font-semibold text-slate-200" x-text="previewPhotoTitle"></h4>
                    <button type="button" @click="previewModalOpen = false" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex max-h-[75vh] items-center justify-center bg-black/90 p-4">
                    <img :src="previewPhotoUrl" :alt="previewPhotoTitle" class="max-h-[70vh] w-auto max-w-full rounded-lg object-contain shadow-lg">
                </div>
                <div class="bg-slate-950 px-4 py-2.5 text-right">
                    <button type="button" @click="previewModalOpen = false" class="rounded-lg bg-slate-800 px-4 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-slate-700">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
