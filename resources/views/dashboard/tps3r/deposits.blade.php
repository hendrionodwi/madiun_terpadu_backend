<x-layouts::app :title="__('Setoran Sampah TPS3R')">
    <div x-data="{
        inspectModalOpen: false,
        activeDeposit: null,
        previewModalOpen: false,
        previewPhotoUrl: null,
        previewPhotoTitle: '',
        openInspect(deposit) {
            this.activeDeposit = deposit;
            this.inspectModalOpen = true;
        },
        openPhotoPreview(url, title) {
            this.previewPhotoUrl = url;
            this.previewPhotoTitle = title || 'Bukti Penimbangan Sampah';
            this.previewModalOpen = true;
        }
    }" class="mx-auto flex max-w-7xl flex-col gap-6 p-2 md:p-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Setoran Sampah TPS3R</h1>
            <p class="text-sm text-zinc-500">Catat penerimaan, periksa foto bukti timbangan warga dari armada truk, dan konfirmasi setoran.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Catat Penerimaan Setoran Mandiri</h2>
            <form method="POST" action="{{ route('dashboard.tps3r.store-deposit') }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    Sumber sampah
                    <select name="sumber_sampah" required class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">
                        <option value="">Pilih sumber</option>
                        <option value="armada_kelurahan">Armada kelurahan</option>
                        <option value="warga_mandiri">Warga mandiri</option>
                        <option value="pemrakarsa_komersial">Pemrakarsa/komersial</option>
                    </select>
                    @error('sumber_sampah')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    Jenis sampah
                    <select name="jenis_sampah" required class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">
                        <option value="">Pilih jenis</option>
                        <option value="organik">Organik</option>
                        <option value="anorganik_daur_ulang">Anorganik daur ulang</option>
                        <option value="residu">Residu</option>
                    </select>
                    @error('jenis_sampah')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    Berat (kg)
                    <input type="number" name="berat_kg" value="{{ old('berat_kg') }}" min="0.1" step="0.1" required class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">
                    @error('berat_kg')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    Tanggal setoran
                    <input type="date" name="tanggal_setor" value="{{ old('tanggal_setor', now()->toDateString()) }}" required class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">
                </label>
                <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 md:col-span-2">
                    Catatan
                    <input type="text" name="catatan" value="{{ old('catatan') }}" maxlength="500" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">
                </label>
                <div class="flex items-end">
                    <button class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">Simpan setoran</button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                <label class="grid gap-2 text-sm font-medium">Dari tanggal<input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <label class="grid gap-2 text-sm font-medium">Sampai tanggal<input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <div class="flex gap-3 lg:col-span-2">
                    <button class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white">Terapkan filter</button>
                    <a href="{{ route('dashboard.tps3r.deposits') }}" class="rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold">Bulan berjalan</a>
                </div>
            </form>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-3 py-3">Tanggal & Resi</th>
                            <th class="px-3 py-3">Sumber & Rute</th>
                            <th class="px-3 py-3">Penyetor / Petugas</th>
                            <th class="px-3 py-3">Jenis</th>
                            <th class="px-3 py-3 text-right">Berat Truk</th>
                            <th class="px-3 py-3 text-center">Muatan Warga</th>
                            <th class="px-3 py-3 text-center">Status</th>
                            <th class="px-3 py-3 text-center">Aksi / Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($deposits as $deposit)
                            @php
                                $depositPayload = [
                                    'id' => $deposit->id,
                                    'nomor_resi' => $deposit->nomor_resi,
                                    'tanggal_setor' => $deposit->tanggal_setor->format('d M Y'),
                                    'petugas' => $deposit->petugas?->name ?? 'Mandiri / Pengelola',
                                    'sumber_sampah' => $deposit->sumber_sampah,
                                    'route_code' => $deposit->route_code,
                                    'berat_kg' => (float) $deposit->berat_kg,
                                    'berat_konfirmasi_kg' => $deposit->berat_konfirmasi_kg ? (float) $deposit->berat_konfirmasi_kg : null,
                                    'status' => $deposit->status,
                                    'catatan' => $deposit->catatan,
                                    'weighings' => $deposit->weighings->map(fn($w) => [
                                        'id' => $w->id,
                                        'warga_name' => $w->warga?->name ?? 'Warga Kota Madiun',
                                        'warga_phone' => $w->warga?->phone_number ?? '-',
                                        'jenis_sampah' => $w->jenis_sampah,
                                        'berat_kg' => (float) $w->berat_kg,
                                        'poin_didapat' => $w->poin_didapat,
                                        'foto_bukti' => $w->foto_bukti,
                                        'catatan' => $w->catatan,
                                    ]),
                                ];
                            @endphp
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $deposit->tanggal_setor->format('d M Y') }}</div>
                                    <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="font-mono text-xs text-teal-700 dark:text-teal-400 font-semibold hover:underline inline-flex items-center gap-1" title="Lihat Halaman Detail Setoran">
                                        <span>{{ $deposit->nomor_resi }}</span>
                                        <svg class="size-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="capitalize text-zinc-800 dark:text-zinc-200">{{ str_replace('_', ' ', $deposit->sumber_sampah) }}</div>
                                    @if($deposit->route_code)
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                            {{ $deposit->route_code }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-zinc-700 dark:text-zinc-200 font-medium">
                                    {{ $deposit->petugas?->name ?? 'Mandiri / Pengelola' }}
                                </td>
                                <td class="px-3 py-3 capitalize">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                        @if($deposit->jenis_sampah === 'organik') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200
                                        @elseif($deposit->jenis_sampah === 'anorganik_daur_ulang') bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-200
                                        @else bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-200 @endif">
                                        {{ str_replace('_', ' ', $deposit->jenis_sampah) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right font-semibold whitespace-nowrap">
                                    {{ number_format($deposit->berat_konfirmasi_kg ?? $deposit->berat_kg, 1, ',', '.') }} kg
                                    @if($deposit->berat_konfirmasi_kg && $deposit->berat_konfirmasi_kg != $deposit->berat_kg)
                                        <div class="text-[10px] text-zinc-500 line-through">{{ number_format($deposit->berat_kg, 1, ',', '.') }} kg</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    @if($deposit->weighings->isNotEmpty())
                                        <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>{{ $deposit->weighings->count() }} Timbangan</span>
                                        </a>
                                    @else
                                        <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="text-xs text-zinc-400 hover:underline">Direct Truk</a>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    @if($deposit->status === 'menunggu_konfirmasi')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-200">
                                            Menunggu Konfirmasi
                                        </span>
                                    @elseif($deposit->status === 'diterima')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200">
                                            Diterima
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-950/60 dark:text-red-200">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    @if($deposit->status === 'menunggu_konfirmasi')
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="rounded-lg bg-teal-700 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-teal-800">
                                                Verifikasi & Detail
                                            </a>
                                        </div>
                                    @elseif($deposit->status === 'diterima')
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                                ✓ Terverifikasi
                                            </span>
                                            <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="text-[11px] font-semibold text-teal-700 dark:text-teal-400 hover:underline">
                                                Detail →
                                            </a>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-xs font-medium text-red-600 dark:text-red-400">
                                                ✕ Ditolak
                                            </span>
                                            <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="text-[11px] font-semibold text-teal-700 dark:text-teal-400 hover:underline">
                                                Detail →
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-8 text-center text-zinc-500 dark:text-zinc-400">Tidak ada setoran pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $deposits->links() }}</div>
        </section>

        <!-- Modal Inspeksi Detail Muatan Truk & Foto Bukti Timbangan Warga -->
        <div x-show="inspectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @keydown.escape.window="inspectModalOpen = false">
            <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="inspectModalOpen = false">
                <!-- Header Modal -->
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/50">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <span>Inspeksi Rincian Muatan Truk</span>
                            <span class="font-mono text-xs px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300" x-text="activeDeposit ? activeDeposit.nomor_resi : ''"></span>
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            Penyetor: <span class="font-semibold text-zinc-700 dark:text-zinc-300" x-text="activeDeposit ? activeDeposit.petugas : ''"></span> • Tanggal: <span x-text="activeDeposit ? activeDeposit.tanggal_setor : ''"></span>
                        </p>
                    </div>
                    <button type="button" @click="inspectModalOpen = false" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-6 overflow-y-auto space-y-4">
                    <template x-if="activeDeposit && activeDeposit.weighings && activeDeposit.weighings.length > 0">
                        <div class="space-y-3">
                            <div class="text-xs text-zinc-500 font-semibold uppercase tracking-wider">
                                Daftar Timbangan Warga Yang Diangkut (<span x-text="activeDeposit.weighings.length"></span> Item):
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="(item, idx) in activeDeposit.weighings" :key="item.id">
                                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-3 bg-zinc-50/50 dark:bg-zinc-800/40 text-xs flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm" x-text="item.warga_name"></span>
                                                <span class="px-2 py-0.5 rounded font-mono font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/60" x-text="item.berat_kg + ' kg'"></span>
                                            </div>
                                            <div class="text-zinc-500 mb-2">
                                                Kontak: <span x-text="item.warga_phone"></span> • Jenis: <span class="font-semibold capitalize" x-text="item.jenis_sampah.replace('_', ' ')"></span>
                                            </div>
                                            <template x-if="item.catatan">
                                                <p class="text-[11px] text-zinc-600 dark:text-zinc-400 italic mb-2" x-text="'Catatan: ' + item.catatan"></p>
                                            </template>
                                        </div>

                                        <!-- Foto Bukti Timbangan -->
                                        <div class="mt-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                            <div class="text-[11px] font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5 flex items-center gap-1">
                                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Foto Bukti Timbangan:
                                            </div>
                                            <template x-if="item.foto_bukti">
                                                <div class="relative group cursor-pointer overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 max-h-36 bg-zinc-900"
                                                     @click="openPhotoPreview(item.foto_bukti.startsWith('http') ? item.foto_bukti : ('/storage/' + item.foto_bukti), 'Bukti Timbangan: ' + item.warga_name + ' (' + item.berat_kg + ' kg)')">
                                                    <img :src="item.foto_bukti.startsWith('http') ? item.foto_bukti : ('/storage/' + item.foto_bukti)"
                                                         alt="Foto Timbangan Warga"
                                                         class="w-full h-32 object-cover transition group-hover:scale-105"
                                                         onerror="this.onerror=null; this.src='/images/placeholder-timbangan.png';">
                                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[11px] font-semibold transition gap-1.5">
                                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                        <span>Klik Perbesar</span>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="!item.foto_bukti">
                                                <div class="p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-400 text-center text-[11px] italic">
                                                    Tidak ada lampiran foto timbangan.
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="!activeDeposit || !activeDeposit.weighings || activeDeposit.weighings.length === 0">
                        <div class="py-8 text-center text-zinc-400 text-xs">
                            Tidak ada data penimbangan warga spesifik yang terhubung dengan muatan setoran truk ini.
                        </div>
                    </template>
                </div>

                <!-- Footer Modal -->
                <div class="px-6 py-3 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 flex items-center justify-between">
                    <a :href="'/dashboard/tps3r/deposits/' + (activeDeposit ? activeDeposit.id : '')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-700 text-white text-xs font-bold hover:bg-teal-800 transition shadow-sm">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Buka Halaman Lengkap</span>
                    </a>
                    <button type="button" @click="inspectModalOpen = false" class="px-4 py-2 rounded-xl bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-semibold text-xs hover:bg-zinc-300 dark:hover:bg-zinc-600 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Lightbox Preview Foto Timbangan -->
        <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" @keydown.escape.window="previewModalOpen = false">
            <div class="w-full max-w-2xl rounded-2xl bg-zinc-900 border border-zinc-700 shadow-2xl overflow-hidden flex flex-col" @click.outside="previewModalOpen = false">
                <!-- Header Lightbox -->
                <div class="px-5 py-3.5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950/80">
                    <div class="flex items-center gap-2 text-zinc-200">
                        <svg class="size-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <h4 class="text-sm font-bold truncate max-w-md text-zinc-100" x-text="previewPhotoTitle"></h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="previewPhotoUrl" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-zinc-800 hover:bg-zinc-700 text-teal-400 hover:text-teal-300 transition">
                            <span>Buka Tab Baru</span>
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <button type="button" @click="previewModalOpen = false" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Foto Area -->
                <div class="p-4 flex items-center justify-center bg-black/60 max-h-[75vh] overflow-hidden">
                    <img :src="previewPhotoUrl" alt="Bukti Foto Timbangan" class="max-h-[70vh] w-auto max-w-full rounded-lg shadow-lg object-contain" onerror="this.onerror=null; this.src='/images/placeholder-timbangan.png';">
                </div>

                <!-- Footer Lightbox -->
                <div class="px-5 py-2.5 border-t border-zinc-800 bg-zinc-950/80 flex items-center justify-between text-xs text-zinc-400">
                    <span>Dokumentasi resmi terverifikasi DLH Kota Madiun</span>
                    <button type="button" @click="previewModalOpen = false" class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
