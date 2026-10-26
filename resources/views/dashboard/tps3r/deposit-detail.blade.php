<x-layouts::app :title="__('Detail Laporan Setoran Sampah - ' . $deposit->nomor_resi)">
    <div x-data="{
        previewModalOpen: false,
        previewPhotoUrl: null,
        previewPhotoTitle: '',
        openPhotoPreview(url, title) {
            this.previewPhotoUrl = url;
            this.previewPhotoTitle = title || 'Bukti Penimbangan Sampah';
            this.previewModalOpen = true;
        }
    }" class="mx-auto flex max-w-7xl flex-col gap-6 p-2 md:p-4 print:p-0">

        <!-- Navigation Breadcrumbs & Actions -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <div class="flex items-center gap-2 text-sm text-zinc-500">
                <a href="{{ route('dashboard.tps3r.volume-recap') }}" class="hover:text-teal-700 dark:hover:text-teal-400 flex items-center gap-1 font-medium transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Rekap Volume TPS3R</span>
                </a>
                <span>/</span>
                <a href="{{ route('dashboard.tps3r.deposits') }}" class="hover:text-teal-700 dark:hover:text-teal-400 font-medium transition">
                    Setoran Sampah
                </a>
                <span>/</span>
                <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $deposit->nomor_resi }}</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs font-bold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Manifest</span>
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200 print:hidden">
                {{ session('status') }}
            </div>
        @endif

        <!-- Page Header Card -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">
                            Manifest Setoran Sampah
                        </h1>
                        <span class="rounded-lg bg-teal-100 px-2.5 py-1 font-mono text-sm font-bold text-teal-800 dark:bg-teal-950 dark:text-teal-300">
                            {{ $deposit->nomor_resi }}
                        </span>
                        @if ($deposit->status === 'diterima')
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Diterima & Terverifikasi
                            </span>
                        @elseif ($deposit->status === 'menunggu_konfirmasi')
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Menunggu Verifikasi TPS3R
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Ditolak
                            </span>
                        @endif
                    </div>
                    <p class="mt-1.5 text-xs text-zinc-500">
                        Dokumen manifest logistik persampahan terintegrasi DLH Kota Madiun & TPS3R Kelurahan.
                    </p>
                </div>

                <div class="flex items-center gap-4 text-right">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-zinc-400">Total Berat Setoran</div>
                        <div class="text-2xl font-black text-teal-700 dark:text-teal-400">
                            {{ number_format($deposit->berat_konfirmasi_kg ?? $deposit->berat_kg, 1, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">kg</span>
                        </div>
                        @if($deposit->berat_konfirmasi_kg && $deposit->berat_konfirmasi_kg != $deposit->berat_kg)
                            <div class="text-[11px] text-zinc-400 line-through">
                                Input armada: {{ number_format($deposit->berat_kg, 1, ',', '.') }} kg
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Meta Details Grid -->
            <div class="mt-6 grid grid-cols-2 gap-4 border-t border-zinc-100 pt-5 sm:grid-cols-4 dark:border-zinc-800 text-xs">
                <div>
                    <span class="text-zinc-400">Tanggal & Waktu Setor</span>
                    <p class="mt-0.5 font-semibold text-zinc-800 dark:text-zinc-200">
                        {{ $deposit->tanggal_setor->format('d M Y') }}
                        <span class="text-[11px] font-normal text-zinc-400">({{ $deposit->created_at->format('H:i') }} WIB)</span>
                    </p>
                </div>
                <div>
                    <span class="text-zinc-400">Petugas Armada / Sopir</span>
                    <p class="mt-0.5 font-semibold text-zinc-800 dark:text-zinc-200">
                        {{ $deposit->petugas?->name ?? 'Mandiri / Non-Armada' }}
                        @if($deposit->petugas?->petugasLapanganProfile?->nomor_kendaraan)
                            <span class="block text-[11px] font-mono text-teal-600 dark:text-teal-400">
                                {{ $deposit->petugas->petugasLapanganProfile->nomor_kendaraan }}
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-zinc-400">Sumber & Rute Wilayah</span>
                    <p class="mt-0.5 font-semibold capitalize text-zinc-800 dark:text-zinc-200">
                        {{ str_replace('_', ' ', $deposit->sumber_sampah) }}
                        @if($deposit->route_code)
                            <span class="ml-1 inline-block rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[10px] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                Rute: {{ $deposit->route_code }}
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-zinc-400">Fasilitas TPS3R Tujuan</span>
                    <p class="mt-0.5 font-semibold text-zinc-800 dark:text-zinc-200">
                        {{ $deposit->tps3rProfile?->nama_tps3r ?? 'TPS3R Kota Madiun' }}
                        <span class="block text-[11px] font-normal text-zinc-500">
                            Kel. {{ $deposit->tps3rProfile?->kelurahan ?? 'Kota Madiun' }}
                        </span>
                    </p>
                </div>
            </div>

            @if($deposit->catatan || $deposit->catatan_verifikasi)
                <div class="mt-4 rounded-xl bg-zinc-50 p-3.5 text-xs text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300 flex flex-col gap-1.5">
                    @if($deposit->catatan)
                        <div><strong class="text-zinc-700 dark:text-zinc-200">Catatan Pengirim:</strong> {{ $deposit->catatan }}</div>
                    @endif
                    @if($deposit->catatan_verifikasi)
                        <div><strong class="text-teal-700 dark:text-teal-400">Catatan Verifikasi TPS3R:</strong> {{ $deposit->catatan_verifikasi }} (diverifikasi {{ $deposit->verified_at?->format('d M Y H:i') ?? '-' }})</div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Neraca Komposisi 3 Pilar (Organik, Anorganik Daur Ulang, Residu) -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span>Neraca & Komposisi Muatan Truk</span>
                        <span class="text-xs font-normal text-zinc-500">(Standarisasi 3 Pilar DLH Kota Madiun)</span>
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        @if($deposit->berat_organik_pilah_kg !== null || $deposit->berat_anorganik_pilah_kg !== null || $deposit->berat_residu_pilah_kg !== null)
                            <span class="font-semibold text-emerald-700 dark:text-emerald-400">✓ Data bersumber dari hasil Pemilahan Lanjutan di Fasilitas TPS3R.</span>
                        @elseif($deposit->weighings->isNotEmpty())
                            <span class="text-teal-700 dark:text-teal-400 font-medium">Data dihitung dari akumulasi {{ $deposit->weighings->count() }} timbangan warga di sepanjang rute penjemputan.</span>
                        @else
                            <span class="text-zinc-500">Kategori tunggal dicatat langsung oleh armada truk.</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Segmented Progress Bar -->
            <div class="mt-5 h-4 w-full overflow-hidden rounded-full bg-zinc-100 flex dark:bg-zinc-800 shadow-inner">
                @if($composition['organik']['percentage'] > 0)
                    <div style="width: {{ $composition['organik']['percentage'] }}%" class="bg-emerald-500 h-full transition-all" title="Organik: {{ $composition['organik']['percentage'] }}%"></div>
                @endif
                @if($composition['anorganik_daur_ulang']['percentage'] > 0)
                    <div style="width: {{ $composition['anorganik_daur_ulang']['percentage'] }}%" class="bg-sky-500 h-full transition-all" title="Anorganik Daur Ulang: {{ $composition['anorganik_daur_ulang']['percentage'] }}%"></div>
                @endif
                @if($composition['residu']['percentage'] > 0)
                    <div style="width: {{ $composition['residu']['percentage'] }}%" class="bg-rose-500 h-full transition-all" title="Residu TPA: {{ $composition['residu']['percentage'] }}%"></div>
                @endif
            </div>

            <!-- Composition Metric Cards -->
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Organik -->
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-sm font-bold text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">🍃</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Organik</span>
                        </div>
                        <span class="text-xs font-extrabold text-emerald-700 dark:text-emerald-300">{{ $composition['organik']['percentage'] }}%</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-xl font-black text-emerald-800 dark:text-emerald-300">
                            {{ number_format($composition['organik']['weight'], 1, ',', '.') }} <span class="text-xs font-semibold text-zinc-500">kg</span>
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">
                            Diproses untuk komposting & pakan maggot BSF
                        </p>
                    </div>
                </div>

                <!-- Anorganik Daur Ulang -->
                <div class="rounded-xl border border-sky-200 bg-sky-50/60 p-4 dark:border-sky-900/40 dark:bg-sky-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-sky-100 text-sm font-bold text-sky-800 dark:bg-sky-900/60 dark:text-sky-300">♻️</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Anorganik Daur Ulang</span>
                        </div>
                        <span class="text-xs font-extrabold text-sky-700 dark:text-sky-300">{{ $composition['anorganik_daur_ulang']['percentage'] }}%</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-xl font-black text-sky-800 dark:text-sky-300">
                            {{ number_format($composition['anorganik_daur_ulang']['weight'], 1, ',', '.') }} <span class="text-xs font-semibold text-zinc-500">kg</span>
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">
                            Plastik, kertas, botol kaca, logam & kardus bernilai ekonomi
                        </p>
                    </div>
                </div>

                <!-- Residu -->
                <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-rose-100 text-sm font-bold text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">🗑️</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300">Residu (TPA)</span>
                        </div>
                        <span class="text-xs font-extrabold text-rose-700 dark:text-rose-300">{{ $composition['residu']['percentage'] }}%</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-xl font-black text-rose-800 dark:text-rose-300">
                            {{ number_format($composition['residu']['weight'], 1, ',', '.') }} <span class="text-xs font-semibold text-zinc-500">kg</span>
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">
                            Sampah tidak dapat diolah, ditransfer ke TPA Winongo
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Form Pemilahan Lanjutan di TPS3R (Material Recovery Facility) -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 print:hidden">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span>Verifikasi & Koreksi Pemilahan Lanjutan TPS3R</span>
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Pengelola TPS3R dapat mencatat berat aktual hasil pemilahan material recovery (organik, anorganik terpilah, dan residu) saat memverifikasi muatan truk.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('dashboard.tps3r.verify-deposit', $deposit) }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                @method('PATCH')

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Status Verifikasi Setoran
                    <select name="status" required class="rounded-xl border-zinc-300 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                        <option value="diterima" @selected($deposit->status === 'diterima' || $deposit->status === 'menunggu_konfirmasi')>Diterima & Selesai Timbang</option>
                        <option value="ditolak" @selected($deposit->status === 'ditolak')>Tolak Setoran</option>
                    </select>
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Total Berat Konfirmasi (kg)
                    <input type="number" step="0.1" min="0.1" name="berat_konfirmasi_kg"
                        value="{{ old('berat_konfirmasi_kg', $deposit->berat_konfirmasi_kg ?? $deposit->berat_kg) }}"
                        required class="rounded-xl border-zinc-300 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                    🍃 Organik Terpilah (kg)
                    <input type="number" step="0.1" min="0" name="berat_organik_pilah_kg"
                        value="{{ old('berat_organik_pilah_kg', $deposit->berat_organik_pilah_kg ?? ($composition['organik']['weight'] > 0 ? $composition['organik']['weight'] : null)) }}"
                        placeholder="Contoh: 35.5"
                        class="rounded-xl border-emerald-300 text-xs dark:border-emerald-700 dark:bg-zinc-800">
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-sky-800 dark:text-sky-300">
                    ♻️ Anorganik Daur Ulang Terpilah (kg)
                    <input type="number" step="0.1" min="0" name="berat_anorganik_pilah_kg"
                        value="{{ old('berat_anorganik_pilah_kg', $deposit->berat_anorganik_pilah_kg ?? ($composition['anorganik_daur_ulang']['weight'] > 0 ? $composition['anorganik_daur_ulang']['weight'] : null)) }}"
                        placeholder="Contoh: 25.0"
                        class="rounded-xl border-sky-300 text-xs dark:border-sky-700 dark:bg-zinc-800">
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-rose-800 dark:text-rose-300">
                    🗑️ Residu TPA Terpilah (kg)
                    <input type="number" step="0.1" min="0" name="berat_residu_pilah_kg"
                        value="{{ old('berat_residu_pilah_kg', $deposit->berat_residu_pilah_kg ?? ($composition['residu']['weight'] > 0 ? $composition['residu']['weight'] : null)) }}"
                        placeholder="Contoh: 10.0"
                        class="rounded-xl border-rose-300 text-xs dark:border-rose-700 dark:bg-zinc-800">
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 sm:col-span-2 lg:col-span-2">
                    Catatan Verifikasi / Kondisi Muatan
                    <input type="text" maxlength="500" name="catatan_verifikasi"
                        value="{{ old('catatan_verifikasi', $deposit->catatan_verifikasi) }}"
                        placeholder="Contoh: Muatan sesuai manifest rute, pemilahan organik masuk komposting."
                        class="rounded-xl border-zinc-300 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                </label>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-teal-800 shadow-sm">
                        Simpan Verifikasi & Pemilahan
                    </button>
                </div>
            </form>
        </section>

        <!-- Manifest Sampah Warga (Timbangan Rumah Tangga) -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span>Manifest Sampah Warga Terangkut</span>
                        <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-bold text-teal-700 dark:bg-teal-950 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                            {{ $deposit->weighings->count() }} Rumah Tangga
                        </span>
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Rincian penimbangan sampah langsung dari warga penerima layanan di sepanjang rute armada ini.
                    </p>
                </div>

                <div class="flex items-center gap-3 text-xs text-zinc-500">
                    <div>Total Poin Disalurkan: <strong class="text-teal-700 dark:text-teal-300 font-bold">{{ number_format($deposit->weighings->sum('poin_didapat')) }} Poin</strong></div>
                </div>
            </div>

            @if($deposit->weighings->isNotEmpty())
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 uppercase text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th class="px-3 py-3 w-10 text-center">#</th>
                                <th class="px-3 py-3">Nama Warga & Kontak</th>
                                <th class="px-3 py-3">Alamat / Kelurahan</th>
                                <th class="px-3 py-3">Kategori Sampah</th>
                                <th class="px-3 py-3 text-right">Berat (kg)</th>
                                <th class="px-3 py-3 text-right">Poin Reward</th>
                                <th class="px-3 py-3 text-center">Foto Bukti Timbangan</th>
                                <th class="px-3 py-3">Catatan Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($deposit->weighings as $index => $w)
                                @php
                                    $pillar = \App\Models\MasterData::resolvePillar($w->jenis_sampah);
                                    $fotoUrl = $w->foto_bukti ? (str_starts_with($w->foto_bukti, 'http') ? $w->foto_bukti : asset('storage/' . $w->foto_bukti)) : null;
                                @endphp
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                                    <td class="px-3 py-3 text-center text-zinc-400 font-mono">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                            {{ $w->warga?->name ?? 'Warga Kota Madiun' }}
                                        </div>
                                        <div class="font-mono text-[11px] text-zinc-500">
                                            {{ $w->warga?->phone_number ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-zinc-600 dark:text-zinc-400">
                                        {{ $w->warga?->wargaProfile?->alamat ?? 'Kel. ' . ($w->warga?->wargaProfile?->kelurahan ?? '-') }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-semibold capitalize text-zinc-800 dark:text-zinc-200">
                                            {{ str_replace('_', ' ', $w->jenis_sampah) }}
                                        </div>
                                        <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-medium
                                            @if($pillar === 'organik') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300
                                            @elseif($pillar === 'anorganik_daur_ulang') bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300
                                            @else bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 @endif">
                                            {{ str_replace('_', ' ', $pillar) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right font-black text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                        {{ number_format($w->berat_kg, 1, ',', '.') }} kg
                                    </td>
                                    <td class="px-3 py-3 text-right font-bold text-teal-700 dark:text-teal-400 whitespace-nowrap">
                                        +{{ number_format($w->poin_didapat) }}
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        @if($fotoUrl)
                                            <button type="button" @click="openPhotoPreview('{{ $fotoUrl }}', 'Bukti Timbangan {{ addslashes($w->warga?->name ?? 'Warga') }} ({{ $w->berat_kg }} kg {{ $w->jenis_sampah }})')"
                                                class="group relative inline-block overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                <img src="{{ $fotoUrl }}" alt="Bukti Timbangan" class="size-11 object-cover transition group-hover:scale-105" loading="lazy">
                                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                    <svg class="size-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                </div>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] text-zinc-400 italic">
                                                Tanpa Foto
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-zinc-500 italic max-w-xs truncate">
                                        {{ $w->catatan ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50/80 font-bold dark:bg-zinc-800/40">
                            <tr>
                                <td colspan="4" class="px-3 py-3 text-right uppercase text-zinc-500">Total Akumulasi Manifest:</td>
                                <td class="px-3 py-3 text-right text-teal-800 dark:text-teal-300 font-black">
                                    {{ number_format($deposit->weighings->sum('berat_kg'), 1, ',', '.') }} kg
                                </td>
                                <td class="px-3 py-3 text-right text-teal-800 dark:text-teal-300 font-black">
                                    +{{ number_format($deposit->weighings->sum('poin_didapat')) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                    <p class="text-xs text-zinc-500">
                        Setoran ini dicatat langsung per kontainer / rit truk tanpa lampiran data timbangan rumah tangga individual.
                    </p>
                </div>
            @endif
        </section>

        <!-- Lightbox Modal Foto Bukti Timbangan Warga -->
        <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" @keydown.escape.window="previewModalOpen = false">
            <div class="relative max-w-3xl w-full bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-800" @click.outside="previewModalOpen = false">
                <div class="px-4 py-3 bg-zinc-950 flex items-center justify-between border-b border-zinc-800">
                    <h4 class="text-sm font-semibold text-zinc-200" x-text="previewPhotoTitle"></h4>
                    <button type="button" @click="previewModalOpen = false" class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 flex items-center justify-center bg-black/90 max-h-[75vh]">
                    <img :src="previewPhotoUrl" :alt="previewPhotoTitle" class="max-h-[70vh] w-auto max-w-full rounded-lg object-contain shadow-lg">
                </div>
                <div class="px-4 py-2.5 bg-zinc-950 text-right">
                    <button type="button" @click="previewModalOpen = false" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-zinc-800 text-zinc-200 hover:bg-zinc-700 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts::app>
