<x-layouts::app :title="__('Pencairan Dana & Saldo Poin Warga - Admin DLH')">
    @php
        $fieldClass = 'min-h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-900 outline-none transition focus:border-civic-500 focus:bg-white focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div x-data="{ showAllocationModal: false }" class="civic-page">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Pencairan & Poin"
            eyebrow="Keuangan Reward"
            title="Sirkulasi Poin & Anggaran Kas Reward"
            description="Monitoring perolehan reward pilah sampah, kontrol kas APBD/daur ulang, dan persetujuan pencairan poin warga."
            icon="wallet"
        >
            <x-slot:actions>
                <span class="civic-status-success !px-3.5 !py-2 !text-xs">
                    Konversi: 1 Poin = Rp {{ number_format($pointRate, 0, ',', '.') }}
                </span>
                <button type="button" @click="showAllocationModal = true" class="civic-button-primary !min-h-10 !py-2 !text-xs">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    + Tambah Anggaran Kas
                </button>
                <button type="button" onclick="window.print()" class="civic-button-secondary !min-h-10 !py-2 !text-xs print:hidden">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak Laporan
                </button>
            </x-slot:actions>
        </x-civic.subpage-header>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Kartu Keuangan & Poin -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Saldo Kas Tersedia (Highlight Utama) -->
            <div class="civic-hero !rounded-2xl !p-5">
                <div class="civic-hero-grid" aria-hidden="true"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-civic-200">Saldo Kas Tersedia</span>
                    <span class="size-2 animate-pulse rounded-full bg-emerald-300"></span>
                </div>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-white">
                    Rp {{ number_format($stats['saldo_anggaran_tersedia'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-civic-100/80">Siap dicairkan untuk klaim warga</p>
            </div>

            <!-- Total Anggaran Masuk -->
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Kas Masuk</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-civic-700 dark:text-civic-300">
                    Rp {{ number_format($stats['total_anggaran_masuk'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">APBD, Daur Ulang & CSR</p>
            </div>

            <!-- Total Dana Dicairkan -->
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Realisasi Dicairkan</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-sky-700 dark:text-sky-400">
                    Rp {{ number_format($stats['total_pencairan_sukses'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Klaim disetujui & ditransfer</p>
            </div>

            <!-- Permohonan Menunggu -->
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Menunggu Verifikasi</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                    {{ $stats['pending_requests'] }} <span class="text-sm font-medium text-slate-500">Klaim</span>
                </h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Nominal: Rp {{ number_format($stats['total_pending_amount'], 0, ',', '.') }}</p>
            </div>

            <!-- Saldo Poin Warga Beredar -->
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Poin Beredar (Liabilitas)</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-slate-950 dark:text-white">
                    {{ number_format($stats['total_poin_beredar'], 0, ',', '.') }} <span class="text-xs font-medium text-slate-500">Pts</span>
                </h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">≈ Rp {{ number_format($stats['total_poin_beredar'] * $pointRate, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Filter & Pencarian Laporan Transaksi -->
        <div class="civic-card flex flex-col justify-between gap-4 p-4 text-xs md:flex-row md:items-center print:hidden">
            <!-- Filter Status Tab -->
            <div id="pointsStatusTabs" data-ajax-targets="#pointsStatusTabs,#pointsFilterResetWrap,#pointsWithdrawalsTable" class="civic-filter-rail">
                <a
                    href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => ''])) }}"
                    data-ajax-link
                    class="civic-filter-chip !text-xs {{ ! $currentStatus ? 'civic-filter-chip-active' : '' }}"
                >
                    Semua ({{ $statusCounts['all'] }})
                </a>
                <a
                    href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
                    data-ajax-link
                    class="civic-filter-chip !text-xs {{ $currentStatus === 'pending' ? 'civic-filter-chip-active' : '' }}"
                >
                    Menunggu ({{ $statusCounts['pending'] }})
                </a>
                <a
                    href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}"
                    data-ajax-link
                    class="civic-filter-chip !text-xs {{ $currentStatus === 'approved' ? 'civic-filter-chip-active' : '' }}"
                >
                    Disetujui ({{ $statusCounts['approved'] }})
                </a>
                <a
                    href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
                    data-ajax-link
                    class="civic-filter-chip !text-xs {{ $currentStatus === 'rejected' ? 'civic-filter-chip-active' : '' }}"
                >
                    Ditolak ({{ $statusCounts['rejected'] }})
                </a>
            </div>

            <!-- Form Pencarian & Tanggal -->
            <form id="pointsFilterForm" method="GET" action="{{ route('dashboard.admin.points') }}" data-ajax-form data-ajax-targets="#pointsStatusTabs,#pointsFilterResetWrap,#pointsWithdrawalsTable" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="status" value="{{ $currentStatus ?? '' }}">
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="{{ $fieldClass }}" title="Dari Tanggal">
                <span class="text-slate-400">s/d</span>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="{{ $fieldClass }}" title="Sampai Tanggal">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama warga..." class="{{ $fieldClass }} w-36 sm:w-44">
                <button type="submit" class="civic-button-primary !min-h-10 !px-3.5 !py-1.5 !text-xs">
                    Filter
                </button>
                <span id="pointsFilterResetWrap">
                    @if ($search || $dateFrom || $dateTo || $currentStatus)
                        <a href="{{ route('dashboard.admin.points') }}" data-ajax-link data-ajax-targets="#pointsStatusTabs,#pointsFilterResetWrap,#pointsWithdrawalsTable" class="px-2 py-1.5 text-slate-500 transition hover:text-slate-800 dark:hover:text-slate-200" title="Reset Filter">✕</a>
                    @endif
                </span>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Tabel Permohonan Pencairan -->
            <div id="pointsWithdrawalsTable" data-ajax-zone="admin-points-withdrawals" data-ajax-targets="#pointsStatusTabs,#pointsFilterResetWrap,#pointsWithdrawalsTable" class="civic-card overflow-hidden text-xs lg:col-span-2">
                <div class="civic-section-header">
                    <div>
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Daftar Transaksi Penukaran Poin Warga</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Laporan transaksi pencairan reward pilah sampah ke saldo rupiah / e-wallet.</p>
                    </div>
                </div>

                <div class="civic-table-wrap rounded-none border-0">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>Warga Pemohon</th>
                                <th>Poin & Nilai Rupiah</th>
                                <th>Tujuan Transfer</th>
                                <th>Status & Verifikasi</th>
                                <th class="text-right print:hidden">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($withdrawals as $w)
                                <tr>
                                    <td>
                                        <p class="font-bold text-slate-950 dark:text-white">{{ $w->user->name ?? 'Warga' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $w->user->phone_number ?? '-' }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $w->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    </td>
                                    <td>
                                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ number_format($w->points_used ?? 0) }} Poin</span>
                                        <p class="text-xs font-extrabold text-civic-700 dark:text-civic-300">Rp {{ number_format($w->amount, 0, ',', '.') }}</p>
                                    </td>
                                    <td>
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $w->destination_type) }}</span>
                                        <p class="mt-1 font-mono font-semibold text-slate-700 dark:text-slate-300">{{ $w->destination_account ?? '-' }}</p>
                                        @if ($w->requester_note)
                                            <p class="mt-0.5 text-xs italic text-slate-400">Catatan: {{ $w->requester_note }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="uppercase {{ $w->status === 'approved' ? 'civic-status-success' : ($w->status === 'rejected' ? 'civic-status-danger' : 'civic-status-warning') }}">
                                            {{ $w->status }}
                                        </span>
                                        @if ($w->status !== 'pending' && $w->reviewer)
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                Oleh: <strong class="text-slate-700 dark:text-slate-300">{{ $w->reviewer->name }}</strong>
                                            </p>
                                            <p class="text-xs text-slate-400">{{ $w->reviewed_at ? $w->reviewed_at->translatedFormat('d M Y, H:i') : '' }}</p>
                                        @endif
                                        @if ($w->admin_note)
                                            <p class="mt-0.5 text-xs italic text-slate-500">"{{ $w->admin_note }}"</p>
                                        @endif
                                    </td>
                                    <td class="text-right print:hidden">
                                        @if ($w->status === 'pending')
                                            <form method="POST" action="{{ route('dashboard.admin.points.update', $w) }}" class="inline-flex flex-col justify-end gap-1 sm:flex-row">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="status" value="approved" class="rounded-lg bg-civic-700 px-2.5 py-1.5 text-xs font-bold text-white transition hover:bg-civic-800" title="Setujui dan transfer dana">
                                                    ✓ Setujui
                                                </button>
                                                <button type="submit" name="status" value="rejected" class="rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white transition hover:bg-red-700" title="Tolak dan kembalikan poin">
                                                    ✕ Tolak
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs italic text-slate-400">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-sm text-slate-400">
                                        Belum ada permohonan penukaran poin warga sesuai filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($withdrawals->hasPages())
                    <div class="border-t border-slate-100 p-4 dark:border-slate-800 print:hidden">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            </div>

            <!-- Kolom Kanan: Top Warga & Alokasi Anggaran Terakhir -->
            <div class="space-y-6">
                <!-- Top Warga Teraktif -->
                <div class="civic-card p-5 text-xs">
                    <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Top Warga Peduli Lingkungan</h3>
                    <p class="mt-0.5 mb-4 text-slate-500 dark:text-slate-400">Warga Kota Madiun dengan saldo poin & setoran pilah sampah terbanyak.</p>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($topWarga as $idx => $wp)
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-6 items-center justify-center rounded-full text-xs font-bold {{ $idx === 0 ? 'bg-amber-500 text-white' : ($idx === 1 ? 'bg-slate-300 text-slate-800' : ($idx === 2 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400')) }}">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-slate-950 dark:text-white">{{ $wp->user->name ?? 'Warga' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $wp->kelurahan ?? 'Kota Madiun' }}, {{ $wp->kecamatan }}</p>
                                    </div>
                                </div>
                                <span class="font-display font-extrabold text-amber-600 dark:text-amber-400">
                                    {{ number_format($wp->saldo_poin) }} Pts
                                </span>
                            </div>
                        @empty
                            <div class="py-6 text-center text-slate-400">Belum ada data warga terdaftar.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Info Sirkulasi Ekonomi Sampah -->
                <div class="civic-card space-y-2 border-civic-200 bg-civic-50/70 p-5 text-xs dark:border-civic-900 dark:bg-civic-950/30">
                    <div class="flex items-center gap-2 font-bold text-civic-800 dark:text-civic-300">
                        <flux:icon.information-circle class="size-5 shrink-0" />
                        <span>Siklus Ekonomi Sirkular Madiun</span>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                        Pemberian reward poin kepada warga didukung oleh pendapatan hasil olahan kompos dan daur ulang TPS3R serta pos insentif APBD DLH untuk pengurangan timbulan sampah ke TPA Winongo.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Pemasukan & Alokasi Anggaran Kas Masuk -->
        <div class="civic-card overflow-hidden text-xs">
            <div class="civic-section-header">
                <div>
                    <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Riwayat Alokasi & Pemasukan Anggaran Kas Reward</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pencatatan pos dana masuk (APBD, Penjualan Kompos TPS3R, CSR) sebagai sumber kas pencairan poin warga.</p>
                </div>
                <button type="button" @click="showAllocationModal = true" class="civic-button-secondary !min-h-9 !py-1.5 !text-xs print:hidden">
                    + Input Pemasukan Kas
                </button>
            </div>

            <div class="civic-table-wrap rounded-none border-0">
                <table class="civic-table">
                    <thead>
                        <tr>
                            <th>Tanggal & TA</th>
                            <th>Sumber Anggaran</th>
                            <th>No. Referensi / SK DPA</th>
                            <th>Nominal Masuk</th>
                            <th>Dicatat Oleh</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allocations as $alloc)
                            <tr>
                                <td>
                                    <p class="font-bold text-slate-950 dark:text-white">{{ $alloc->allocated_at->translatedFormat('d M Y') }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">TA {{ $alloc->fiscal_year }}</p>
                                </td>
                                <td>
                                    <span class="{{ $alloc->source_type === 'apbd' ? 'civic-status-info' : ($alloc->source_type === 'penjualan_daur_ulang' ? 'civic-status-success' : 'civic-status bg-civic-100 text-civic-800 dark:bg-civic-950/60 dark:text-civic-300') }}">
                                        {{ $alloc->source_label }}
                                    </span>
                                </td>
                                <td class="font-mono text-slate-700 dark:text-slate-300">
                                    {{ $alloc->reference_number ?? '-' }}
                                </td>
                                <td class="font-display text-sm font-extrabold text-civic-700 dark:text-civic-300">
                                    Rp {{ number_format($alloc->amount, 0, ',', '.') }}
                                </td>
                                <td class="text-slate-600 dark:text-slate-400">
                                    {{ $alloc->creator->name ?? 'Admin DLH' }}
                                </td>
                                <td class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $alloc->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-slate-400">
                                    Belum ada catatan alokasi anggaran kas reward. Silakan tambah alokasi baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Dialog: Tambah Alokasi Anggaran Kas Masuk -->
        <div
            x-show="showAllocationModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm print:hidden"
            @keydown.escape.window="showAllocationModal = false"
        >
            <div @click.away="showAllocationModal = false" class="w-full max-w-lg space-y-4 rounded-2xl border border-slate-200 bg-white p-6 text-xs shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Tambah Alokasi Anggaran Kas Reward</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Catat sumber dana kas masuk untuk menjamin ketersediaan pencairan poin warga.</p>
                    </div>
                    <button type="button" @click="showAllocationModal = false" class="flex size-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800">✕</button>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.points.allocations.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Sumber Anggaran <span class="text-red-500">*</span></label>
                        <select name="source_type" required class="{{ $fieldClass }} w-full">
                            <option value="apbd">Alokasi APBD Kota Madiun</option>
                            <option value="penjualan_daur_ulang">Hasil Penjualan Kompos & Daur Ulang TPS3R</option>
                            <option value="csr_kemitraan">Kemitraan Swasta / CSR / EPR</option>
                            <option value="retribusi">Penerimaan Retribusi Persampahan</option>
                            <option value="lainnya">Lain-lain</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Nominal Anggaran (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" min="1000" step="1000" name="amount" placeholder="misal: 25000000" required class="{{ $fieldClass }} w-full">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Tahun Anggaran (TA) <span class="text-red-500">*</span></label>
                            <input type="number" min="2020" max="2099" name="fiscal_year" value="{{ date('Y') }}" required class="{{ $fieldClass }} w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Tanggal Alokasi <span class="text-red-500">*</span></label>
                            <input type="date" name="allocated_at" value="{{ date('Y-m-d') }}" required class="{{ $fieldClass }} w-full">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">No. Referensi / SK DPA</label>
                            <input type="text" name="reference_number" placeholder="DPA-DLH/2026/01/PILAH" class="{{ $fieldClass }} w-full">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Keterangan / Catatan Peruntukan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan pengalokasian dana insentif..." class="{{ $fieldClass }} w-full"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <button type="button" @click="showAllocationModal = false" class="civic-button-secondary !min-h-9 !py-1.5 !text-xs">
                            Batal
                        </button>
                        <button type="submit" class="civic-button-primary !min-h-9 !py-1.5 !text-xs">
                            Simpan Alokasi Anggaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
