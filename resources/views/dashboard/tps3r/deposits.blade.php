<x-layouts::app :title="__('Setoran Sampah TPS3R')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div
        x-data="{
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
        }"
        class="civic-page mx-auto w-full max-w-7xl"
    >
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Setoran Sampah"
            eyebrow="Penerimaan & Verifikasi"
            title="Setoran Sampah TPS3R"
            description="Catat penerimaan, periksa foto bukti timbangan warga dari armada truk, dan konfirmasi setoran."
            icon="truck"
        />

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <section class="civic-card p-5 sm:p-6">
            <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Catat Penerimaan Setoran Mandiri</h2>
            <form method="POST" action="{{ route('dashboard.tps3r.store-deposit') }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Sumber sampah</span>
                    <select name="sumber_sampah" required class="{{ $fieldClass }}">
                        <option value="">Pilih sumber</option>
                        <option value="armada_kelurahan">Armada kelurahan</option>
                        <option value="warga_mandiri">Warga mandiri</option>
                        <option value="pemrakarsa_komersial">Pemrakarsa/komersial</option>
                    </select>
                    @error('sumber_sampah')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Jenis sampah</span>
                    <select name="jenis_sampah" required class="{{ $fieldClass }}">
                        <option value="">Pilih jenis</option>
                        <option value="organik">Organik</option>
                        <option value="anorganik_daur_ulang">Anorganik daur ulang</option>
                        <option value="residu">Residu</option>
                    </select>
                    @error('jenis_sampah')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Berat neto (kg)</span>
                    <input type="number" name="berat_kg" value="{{ old('berat_kg') }}" min="0.1" step="0.1" required class="{{ $fieldClass }}">
                    @error('berat_kg')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Berat bruto (kg)</span>
                    <input type="number" name="berat_bruto_kg" value="{{ old('berat_bruto_kg') }}" min="0.1" step="0.1" class="{{ $fieldClass }}" placeholder="Muatan dan kendaraan">
                    @error('berat_bruto_kg')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Berat tara (kg)</span>
                    <input type="number" name="berat_tara_kg" value="{{ old('berat_tara_kg') }}" min="0" step="0.1" class="{{ $fieldClass }}" placeholder="Kendaraan kosong">
                    @error('berat_tara_kg')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Kode rute</span>
                    <input type="text" name="route_code" value="{{ old('route_code') }}" maxlength="50" class="{{ $fieldClass }} font-mono" placeholder="RTE-KRT-01">
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Tanggal setoran</span>
                    <input type="date" name="tanggal_setor" value="{{ old('tanggal_setor', now()->toDateString()) }}" required class="{{ $fieldClass }}">
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 md:col-span-2">
                    <span>Catatan</span>
                    <input type="text" name="catatan" value="{{ old('catatan') }}" maxlength="500" class="{{ $fieldClass }}">
                </label>
                <div class="flex items-end">
                    <button type="submit" class="civic-button-primary w-full">Simpan setoran</button>
                </div>
            </form>
        </section>

        <section data-ajax-zone="tps3r-deposits" class="civic-card p-5 sm:p-6">
            <div class="civic-filter-rail mb-4 w-full justify-start">
                @foreach (['all' => 'Semua', 'menunggu_konfirmasi' => 'Menunggu', 'diterima' => 'Diterima', 'perlu_perbaikan' => 'Perlu perbaikan', 'ditolak' => 'Ditolak'] as $statusValue => $statusLabel)
                    <a
                        href="{{ route('dashboard.tps3r.deposits', array_merge(request()->except('page'), ['status' => $statusValue])) }}"
                        data-ajax-link
                        class="civic-filter-chip !text-xs {{ ($statusFilter ?: 'all') === $statusValue ? 'civic-filter-chip-active' : '' }}"
                    >
                        {{ $statusLabel }} ({{ $statusCounts[$statusValue] ?? 0 }})
                    </a>
                @endforeach
            </div>

            <form method="GET" data-ajax-form class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                <input type="hidden" name="status" value="{{ $statusFilter ?? 'all' }}">
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Dari tanggal</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="{{ $fieldClass }}">
                </label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Sampai tanggal</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="{{ $fieldClass }}">
                </label>
                <div class="flex gap-3 lg:col-span-2">
                    <button type="submit" class="civic-button-primary">Terapkan filter</button>
                    <a href="{{ route('dashboard.tps3r.deposits') }}" data-ajax-link class="civic-button-secondary">Bulan berjalan</a>
                </div>
            </form>

            <div data-ajax-region="table">
                <div class="civic-table-wrap mt-5">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>Tanggal & Resi</th>
                                <th>Sumber & Rute</th>
                                <th>Penyetor / Petugas</th>
                                <th>Jenis</th>
                                <th class="text-right">Berat Truk</th>
                                <th class="text-center">Muatan Warga</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi / Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deposits as $deposit)
                                <tr>
                                    <td class="whitespace-nowrap">
                                        <div class="font-semibold text-slate-950 dark:text-white">{{ $deposit->tanggal_setor->format('d M Y') }}</div>
                                        <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="mt-0.5 inline-flex items-center gap-1 font-mono text-xs font-semibold text-civic-700 hover:underline dark:text-civic-300" title="Lihat Halaman Detail Setoran">
                                            <span>{{ $deposit->nomor_resi }}</span>
                                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="capitalize text-slate-800 dark:text-slate-200">{{ str_replace('_', ' ', $deposit->sumber_sampah) }}</div>
                                        @if ($deposit->route_code)
                                            <span class="mt-0.5 inline-block rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                {{ $deposit->route_code }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="font-medium text-slate-700 dark:text-slate-200">
                                        {{ $deposit->petugas?->name ?? 'Mandiri / Pengelola' }}
                                    </td>
                                    <td class="capitalize">
                                        <span class="{{ $deposit->jenis_sampah === 'organik' ? 'civic-status-success' : ($deposit->jenis_sampah === 'anorganik_daur_ulang' ? 'civic-status-info' : 'civic-status-danger') }}">
                                            {{ str_replace('_', ' ', $deposit->jenis_sampah) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-right font-semibold text-slate-950 dark:text-white">
                                        <span class="block">{{ number_format($deposit->berat_konfirmasi_kg ?? $deposit->berat_kg, 1, ',', '.') }} kg neto</span>
                                        @if ($deposit->berat_bruto_kg !== null || $deposit->berat_tara_kg !== null)
                                            <span class="mt-0.5 block text-xs font-medium text-slate-500">
                                                B {{ number_format($deposit->berat_bruto_kg ?? 0, 1, ',', '.') }} · T {{ number_format($deposit->berat_tara_kg ?? 0, 1, ',', '.') }}
                                            </span>
                                        @endif
                                        @if ($deposit->berat_konfirmasi_kg && $deposit->berat_konfirmasi_kg != $deposit->berat_kg)
                                            <div class="text-xs text-slate-400 line-through">{{ number_format($deposit->berat_kg, 1, ',', '.') }} kg</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        @if ($deposit->weighings->isNotEmpty())
                                            <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="civic-button-secondary !min-h-8 !px-2.5 !py-1 !text-xs">
                                                <flux:icon.eye class="size-3.5" />
                                                <span>{{ $deposit->weighings->count() }} Timbangan</span>
                                            </a>
                                        @else
                                            <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="text-xs text-slate-500 hover:underline dark:text-slate-400">Direct Truk</a>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        @if ($deposit->status === 'menunggu_konfirmasi')
                                            <span class="civic-status-warning">Menunggu Konfirmasi</span>
                                        @elseif ($deposit->status === 'diterima')
                                            <span class="civic-status-success">Diterima</span>
                                        @elseif ($deposit->status === 'perlu_perbaikan')
                                            <span class="civic-status-warning">Perlu Perbaikan</span>
                                        @else
                                            <span class="civic-status-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        @if ($deposit->status === 'menunggu_konfirmasi')
                                            <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="civic-button-primary !min-h-8 !px-3 !py-1.5 !text-xs">
                                                Verifikasi & Detail
                                            </a>
                                        @elseif ($deposit->status === 'diterima')
                                            <div class="flex items-center justify-center gap-2">
                                                <span class="text-xs font-medium text-civic-700 dark:text-civic-300">✓ Terverifikasi</span>
                                                <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="civic-link">Detail →</a>
                                            </div>
                                        @elseif ($deposit->status === 'perlu_perbaikan')
                                            <div class="flex items-center justify-center gap-2">
                                                <span class="text-xs font-medium text-amber-700 dark:text-amber-300">Koreksi dibutuhkan</span>
                                                <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="civic-link">Periksa →</a>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center gap-2">
                                                <span class="text-xs font-medium text-red-600 dark:text-red-400">✕ Ditolak</span>
                                                <a href="{{ route('dashboard.tps3r.deposits.show', $deposit) }}" class="civic-link">Detail →</a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Tidak ada setoran pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($deposits->hasPages())
                    <div class="mt-5">{{ $deposits->links() }}</div>
                @endif
            </div>
        </section>

        <!-- Modal Inspeksi Detail Muatan Truk & Foto Bukti Timbangan Warga -->
        <div x-show="inspectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" @keydown.escape.window="inspectModalOpen = false">
            <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900" @click.outside="inspectModalOpen = false">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/50">
                    <div>
                        <h3 class="flex items-center gap-2 font-display text-base font-extrabold text-slate-950 dark:text-white">
                            <span>Inspeksi Rincian Muatan Truk</span>
                            <span class="rounded bg-civic-100 px-2 py-0.5 font-mono text-xs text-civic-800 dark:bg-civic-950 dark:text-civic-300" x-text="activeDeposit ? activeDeposit.nomor_resi : ''"></span>
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            Penyetor: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="activeDeposit ? activeDeposit.petugas : ''"></span> • Tanggal: <span x-text="activeDeposit ? activeDeposit.tanggal_setor : ''"></span>
                        </p>
                    </div>
                    <button type="button" @click="inspectModalOpen = false" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto p-6">
                    <template x-if="activeDeposit && activeDeposit.weighings && activeDeposit.weighings.length > 0">
                        <div class="space-y-3">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Daftar Timbangan Warga Yang Diangkut (<span x-text="activeDeposit.weighings.length"></span> Item):
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <template x-for="(item, idx) in activeDeposit.weighings" :key="item.id">
                                    <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs dark:border-slate-700 dark:bg-slate-800/40">
                                        <div>
                                            <div class="mb-1 flex items-center justify-between">
                                                <span class="text-sm font-bold text-slate-950 dark:text-white" x-text="item.warga_name"></span>
                                                <span class="rounded bg-civic-100 px-2 py-0.5 font-mono font-bold text-civic-800 dark:bg-civic-950/60 dark:text-civic-300" x-text="item.berat_kg + ' kg'"></span>
                                            </div>
                                            <div class="mb-2 text-slate-500">
                                                Kontak: <span x-text="item.warga_phone"></span> • Jenis: <span class="font-semibold capitalize" x-text="item.jenis_sampah.replace('_', ' ')"></span>
                                            </div>
                                            <template x-if="item.catatan">
                                                <p class="mb-2 text-xs italic text-slate-600 dark:text-slate-400" x-text="'Catatan: ' + item.catatan"></p>
                                            </template>
                                        </div>

                                        <div class="mt-2 border-t border-slate-200 pt-2 dark:border-slate-700">
                                            <div class="mb-1.5 flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                <flux:icon.camera class="size-3.5" />
                                                Foto Bukti Timbangan:
                                            </div>
                                            <template x-if="item.foto_bukti">
                                                <div
                                                    class="group relative max-h-36 cursor-pointer overflow-hidden rounded-lg border border-slate-200 bg-slate-900 dark:border-slate-700"
                                                    @click="openPhotoPreview(item.foto_bukti.startsWith('http') ? item.foto_bukti : ('/storage/' + item.foto_bukti), 'Bukti Timbangan: ' + item.warga_name + ' (' + item.berat_kg + ' kg)')"
                                                >
                                                    <img
                                                        :src="item.foto_bukti.startsWith('http') ? item.foto_bukti : ('/storage/' + item.foto_bukti)"
                                                        alt="Foto Timbangan Warga"
                                                        class="h-32 w-full object-cover transition group-hover:scale-105"
                                                        onerror="this.onerror=null; this.src='/images/placeholder-timbangan.png';"
                                                    >
                                                    <div class="absolute inset-0 flex items-center justify-center gap-1.5 bg-black/50 text-xs font-semibold text-white opacity-0 transition group-hover:opacity-100">
                                                        <span>Klik Perbesar</span>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="!item.foto_bukti">
                                                <div class="rounded-lg bg-slate-100 p-2 text-center text-xs italic text-slate-400 dark:bg-slate-800">
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
                        <div class="py-8 text-center text-xs text-slate-400">
                            Tidak ada data penimbangan warga spesifik yang terhubung dengan muatan setoran truk ini.
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-3 dark:border-slate-800 dark:bg-slate-800/50">
                    <a :href="'/dashboard/tps3r/deposits/' + (activeDeposit ? activeDeposit.id : '')" class="civic-button-primary !min-h-9 !px-4 !py-1.5 !text-xs">
                        <span>Buka Halaman Lengkap</span>
                    </a>
                    <button type="button" @click="inspectModalOpen = false" class="civic-button-secondary !min-h-9 !px-4 !py-1.5 !text-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Lightbox Preview Foto Timbangan -->
        <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-md" @keydown.escape.window="previewModalOpen = false">
            <div class="flex w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl" @click.outside="previewModalOpen = false">
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-950/80 px-5 py-3.5">
                    <div class="flex items-center gap-2 text-slate-200">
                        <flux:icon.camera class="size-4 text-emerald-400" />
                        <h4 class="max-w-md truncate text-sm font-bold text-slate-100" x-text="previewPhotoTitle"></h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="previewPhotoUrl" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-slate-800 px-2.5 py-1 text-xs font-semibold text-civic-300 transition hover:bg-slate-700">
                            <span>Buka Tab Baru</span>
                        </a>
                        <button type="button" @click="previewModalOpen = false" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-slate-100">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="flex max-h-[75vh] items-center justify-center overflow-hidden bg-black/60 p-4">
                    <img :src="previewPhotoUrl" alt="Bukti Foto Timbangan" class="max-h-[70vh] w-auto max-w-full rounded-lg object-contain shadow-lg" onerror="this.onerror=null; this.src='/images/placeholder-timbangan.png';">
                </div>

                <div class="flex items-center justify-between border-t border-slate-800 bg-slate-950/80 px-5 py-2.5 text-xs text-slate-400">
                    <span>Dokumentasi resmi terverifikasi DLH Kota Madiun</span>
                    <button type="button" @click="previewModalOpen = false" class="rounded-lg bg-slate-800 px-3 py-1.5 font-semibold text-slate-200 transition hover:bg-slate-700">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
