<x-layouts::app :title="__('Kas Reward & Settlement Poin - Admin DLH')">
    @php
        $fieldClass = 'min-h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-900 outline-none transition focus:border-civic-500 focus:bg-white focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-civic-900';
        $statusTabs = [
            'pending' => 'Verifikasi',
            'approved' => 'Antre Bayar',
            'processing' => 'Diproses',
            'paid' => 'Dibayar',
            'rejected' => 'Ditolak',
            'failed' => 'Gagal',
        ];
    @endphp

    <div x-data="{ showAllocationModal: @js($errors->hasAny(['source_type', 'amount', 'fiscal_year', 'allocated_at', 'reference_number', 'notes'])) }" class="civic-page">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Pencairan & Poin"
            eyebrow="Keuangan Reward"
            title="Kas Reward & Settlement Poin"
            description="Kelola kas reward, pencairan warga, dan settlement penjualan poin TPS3R."
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
                <p class="mt-1 text-xs text-civic-100/80">Setelah komitmen dan realisasi pembayaran</p>
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
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Hanya transaksi berstatus sudah dibayar</p>
            </div>

            <!-- Permohonan Menunggu -->
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Menunggu Verifikasi</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                    {{ $stats['pending_requests'] }} <span class="text-sm font-medium text-slate-500">Klaim</span>
                </h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Dana dicadangkan: Rp {{ number_format($stats['total_pending_amount'], 0, ',', '.') }}</p>
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
                @foreach ($statusTabs as $statusValue => $statusLabel)
                    <a
                        href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => $statusValue])) }}"
                        data-ajax-link
                        class="civic-filter-chip !text-xs {{ $currentStatus === $statusValue ? 'civic-filter-chip-active' : '' }}"
                    >
                        {{ $statusLabel }} ({{ $statusCounts[$statusValue] }})
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian & Tanggal -->
            <form id="pointsFilterForm" method="GET" action="{{ route('dashboard.admin.points') }}" data-ajax-form data-ajax-targets="#pointsStatusTabs,#pointsFilterResetWrap,#pointsWithdrawalsTable" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="status" value="{{ $currentStatus ?? '' }}">
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="{{ $fieldClass }}" title="Dari Tanggal">
                <span class="text-slate-400">s/d</span>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="{{ $fieldClass }}" title="Sampai Tanggal">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama pemohon..." class="{{ $fieldClass }} w-36 sm:w-44">
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
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Daftar Klaim Dana Berbasis Poin</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Mencakup pencairan warga dan settlement marketplace TPS3R.</p>
                    </div>
                </div>

                <div class="civic-table-wrap rounded-none border-0">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>Pemohon & Sumber</th>
                                <th>Nilai Klaim</th>
                                <th>Metode Pencairan</th>
                                <th>Status & Verifikasi</th>
                                <th class="text-right print:hidden">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($withdrawals as $w)
                                @php
                                    $withdrawalStatus = \App\Enums\FundWithdrawalStatus::from($w->status);
                                    $statusClass = match ($withdrawalStatus) {
                                        \App\Enums\FundWithdrawalStatus::Paid => 'civic-status-success',
                                        \App\Enums\FundWithdrawalStatus::Rejected, \App\Enums\FundWithdrawalStatus::Failed => 'civic-status-danger',
                                        \App\Enums\FundWithdrawalStatus::Approved, \App\Enums\FundWithdrawalStatus::Processing => 'civic-status-info',
                                        default => 'civic-status-warning',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <p class="font-bold text-slate-950 dark:text-white">{{ $w->user->name ?? 'Pemohon' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $w->user->phone_number ?? '-' }}</p>
                                        <span class="mt-1 inline-flex rounded-full bg-civic-50 px-2 py-0.5 text-[11px] font-bold text-civic-700 dark:bg-civic-950/50 dark:text-civic-300">
                                            {{ $w->source === 'marketplace_points' ? 'Settlement TPS3R' : 'Penukaran warga' }}
                                        </span>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $w->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    </td>
                                    <td>
                                        @if ($w->source === 'marketplace_points')
                                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $w->marketplace_settlement_items_count }} pesanan poin</span>
                                        @else
                                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ number_format($w->points_used ?? 0) }} Poin</span>
                                        @endif
                                        <p class="text-xs font-extrabold text-civic-700 dark:text-civic-300">Rp {{ number_format($w->amount, 0, ',', '.') }}</p>
                                        @if ($w->source === 'points')
                                            <p class="mt-0.5 text-xs text-slate-400">Kurs: Rp {{ number_format($w->point_conversion_rate ?? $pointRate, 2, ',', '.') }}/poin</p>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $w->destination_type) }}</span>
                                        <p class="mt-1 font-semibold text-slate-700 dark:text-slate-300">{{ $w->destination_provider ?? ($w->destination_type === 'cash' ? 'Loket tunai' : '-') }}</p>
                                        <p class="font-mono text-xs text-slate-500">{{ $w->destination_account ?? $w->cash_pickup_location ?? '-' }}</p>
                                        @if ($w->cash_claim_code)
                                            <p class="mt-1 font-mono text-xs font-extrabold tracking-wider text-civic-700 dark:text-civic-300">{{ $w->cash_claim_code }}</p>
                                        @endif
                                        @if ($w->destination_account_name)
                                            <p class="text-xs text-slate-500">a.n. {{ $w->destination_account_name }}</p>
                                        @endif
                                        @if ($w->requester_note)
                                            <p class="mt-0.5 text-xs italic text-slate-400">Catatan: {{ $w->requester_note }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $statusClass }}">
                                            {{ $withdrawalStatus->label() }}
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
                                        @if ($w->fundAllocation)
                                            <p class="mt-1 text-xs text-slate-500">Dana: {{ $w->fundAllocation->reference_number ?? $w->fundAllocation->source_label }}</p>
                                        @endif
                                        @if ($w->payout_reference)
                                            <p class="mt-1 font-mono text-xs text-slate-500">Ref: {{ $w->payout_reference }}</p>
                                        @endif
                                        @if ($w->payout_proof_path)
                                            <a href="{{ route('dashboard.admin.points.proof', $w) }}" class="mt-1 inline-flex text-xs font-bold text-civic-700 hover:underline">Lihat bukti bayar</a>
                                        @endif
                                        @if ($w->destination_type === 'cash' && $w->cashClaimedBy)
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                Diserahkan oleh {{ $w->cashClaimedBy->name }}
                                                @if ($w->cash_claimed_at)
                                                    · {{ $w->cash_claimed_at->translatedFormat('d M Y, H:i') }}
                                                @endif
                                            </p>
                                        @endif
                                    </td>
                                    <td class="min-w-56 text-right print:hidden">
                                        @if ($w->status === 'pending')
                                            <form method="POST" action="{{ route('dashboard.admin.points.update', $w) }}" class="grid gap-2 text-left">
                                                @csrf
                                                @method('PATCH')
                                                <select name="reward_fund_allocation_id" class="{{ $fieldClass }} w-full" required>
                                                    <option value="">Pilih sumber dana</option>
                                                    @foreach ($allocations as $allocation)
                                                        @php $allocationAvailable = max(0, $allocation->amount - ($allocation->committed_amount ?? 0)); @endphp
                                                        <option value="{{ $allocation->id }}" @disabled($allocationAvailable < $w->amount)>
                                                            {{ $allocation->reference_number ?? $allocation->source_label }} · sisa Rp {{ number_format($allocationAvailable, 0, ',', '.') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input name="admin_note" class="{{ $fieldClass }} w-full" placeholder="Catatan verifikasi (opsional)">
                                                <button type="submit" name="status" value="approved" class="civic-button-primary !min-h-9 !py-1.5 !text-xs" title="Setujui dan masukkan ke antrean pembayaran">
                                                    Setujui klaim
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.admin.points.update', $w) }}" class="mt-2 grid gap-2 text-left">
                                                @csrf
                                                @method('PATCH')
                                                <input name="admin_note" required class="{{ $fieldClass }} w-full" placeholder="Alasan penolakan">
                                                <button type="submit" name="status" value="rejected" class="min-h-9 rounded-xl border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-50">
                                                    {{ $w->source === 'marketplace_points' ? 'Tolak & lepaskan pesanan' : 'Tolak & kembalikan poin' }}
                                                </button>
                                            </form>
                                        @elseif ($w->status === 'approved')
                                            @if ($w->destination_type === 'cash')
                                                <div class="rounded-xl bg-civic-50 p-3 text-left text-xs text-civic-800 ring-1 ring-civic-100 dark:bg-civic-950/40 dark:text-civic-200 dark:ring-civic-900">
                                                    Kode aktif. Layani pencairan melalui formulir loket Admin DLH di samping.
                                                </div>
                                            @else
                                                <form method="POST" action="{{ route('dashboard.admin.points.payout', $w) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="processing">
                                                    <button class="civic-button-primary !min-h-9 !py-1.5 !text-xs">Mulai pembayaran</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('dashboard.admin.points.payout', $w) }}" class="mt-2 grid gap-2 text-left">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="failed">
                                                <input name="failure_note" required class="{{ $fieldClass }} w-full" placeholder="Alasan gagal">
                                                <button class="min-h-9 rounded-xl border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700">Batalkan pembayaran</button>
                                            </form>
                                        @elseif ($w->status === 'processing')
                                            <form method="POST" enctype="multipart/form-data" action="{{ route('dashboard.admin.points.payout', $w) }}" class="grid gap-2 text-left">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="paid">
                                                <input name="payout_reference" required class="{{ $fieldClass }} w-full" placeholder="Referensi transfer/kwitansi">
                                                <input type="file" name="payout_proof" required accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-500 file:mr-2 file:rounded-lg file:border-0 file:bg-civic-50 file:px-2 file:py-1.5 file:font-bold file:text-civic-700">
                                                <input name="payout_note" class="{{ $fieldClass }} w-full" placeholder="Catatan pembayaran">
                                                <button class="civic-button-primary !min-h-9 !py-1.5 !text-xs">Catat sudah dibayar</button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.admin.points.payout', $w) }}" class="mt-2 grid gap-2 text-left">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="failed">
                                                <input name="failure_note" required class="{{ $fieldClass }} w-full" placeholder="Alasan gagal">
                                                <button class="min-h-9 rounded-xl border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700">Tandai gagal</button>
                                            </form>
                                        @else
                                            <span class="text-xs italic text-slate-400">Tidak ada tindakan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-sm text-slate-400">
                                        Belum ada klaim dana berbasis poin sesuai filter ini.
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
                <div class="civic-card p-5 text-xs print:hidden">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-civic-700 dark:text-civic-300">Loket Admin DLH</p>
                            <h3 class="mt-1 font-display text-base font-extrabold text-slate-950 dark:text-white">Pencairan Tunai Warga</h3>
                        </div>
                        <span class="civic-status-info">Kas terpusat</span>
                    </div>
                    <p class="mt-2 leading-relaxed text-slate-500 dark:text-slate-400">Periksa identitas, kode aktif, lalu unggah bukti serah-terima.</p>

                    <form method="POST" enctype="multipart/form-data" action="{{ route('dashboard.admin.points.cash-disbursements.store') }}" class="mt-4 grid gap-3">
                        @csrf
                        <label class="grid gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Kode transaksi</span>
                            <input name="claim_code" value="{{ old('claim_code') }}" required autocomplete="off" placeholder="TUN-XXXXX-XXXXX" class="{{ $fieldClass }} w-full font-mono uppercase tracking-wider">
                        </label>
                        <label class="grid gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Nomor kwitansi kas</span>
                            <input name="payout_reference" value="{{ old('payout_reference') }}" required placeholder="KWT-DLH-2026-0001" class="{{ $fieldClass }} w-full">
                        </label>
                        <label class="grid gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Bukti serah-terima</span>
                            <input type="file" name="payout_proof" required accept=".jpg,.jpeg,.png,.pdf" class="block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-500 file:mr-2 file:min-h-10 file:border-0 file:bg-civic-50 file:px-3 file:font-bold file:text-civic-700 dark:border-slate-700 dark:bg-slate-800">
                        </label>
                        <label class="grid gap-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Catatan <span class="font-normal text-slate-400">(opsional)</span></span>
                            <input name="payout_note" value="{{ old('payout_note') }}" placeholder="Keterangan serah-terima" class="{{ $fieldClass }} w-full">
                        </label>
                        <label class="flex min-h-11 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <input type="checkbox" name="identity_verified" value="1" required @checked(old('identity_verified')) class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-500">
                            <span>Identitas warga sudah cocok</span>
                        </label>
                        <button class="civic-button-primary w-full !min-h-11 !text-xs">Catat uang diserahkan</button>
                    </form>
                </div>

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
                            <th>Terpakai & Tersedia</th>
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
                                <td>
                                    <p class="font-bold text-slate-700 dark:text-slate-200">Terpakai Rp {{ number_format($alloc->committed_amount ?? 0, 0, ',', '.') }}</p>
                                    <p class="text-xs text-civic-700">Sisa Rp {{ number_format(max(0, $alloc->amount - ($alloc->committed_amount ?? 0)), 0, ',', '.') }}</p>
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
                                <td colspan="7" class="py-8 text-center text-sm text-slate-400">
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
                            <option value="apbd" @selected(old('source_type') === 'apbd')>Alokasi APBD Kota Madiun</option>
                            <option value="penjualan_daur_ulang" @selected(old('source_type') === 'penjualan_daur_ulang')>Hasil Penjualan Kompos & Daur Ulang TPS3R</option>
                            <option value="csr_kemitraan" @selected(old('source_type') === 'csr_kemitraan')>Kemitraan Swasta / CSR / EPR</option>
                            <option value="retribusi" @selected(old('source_type') === 'retribusi')>Penerimaan Retribusi Persampahan</option>
                            <option value="lainnya" @selected(old('source_type') === 'lainnya')>Lain-lain</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Nominal Anggaran (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" min="1000" max="9999999999999.99" step="1000" name="amount" value="{{ old('amount') }}" placeholder="misal: 25000000" required class="{{ $fieldClass }} w-full" aria-describedby="amount-help amount-error">
                            <p id="amount-help" class="mt-1 text-xs text-slate-500 dark:text-slate-400">Contoh Rp25 juta: ketik 25000000 tanpa titik.</p>
                            @error('amount')
                                <p id="amount-error" class="mt-1 font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Tahun Anggaran (TA) <span class="text-red-500">*</span></label>
                            <input type="number" min="2020" max="2099" name="fiscal_year" value="{{ old('fiscal_year', date('Y')) }}" required class="{{ $fieldClass }} w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Tanggal Alokasi <span class="text-red-500">*</span></label>
                            <input type="date" name="allocated_at" value="{{ old('allocated_at', date('Y-m-d')) }}" required class="{{ $fieldClass }} w-full">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">No. Referensi / SK DPA</label>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="DPA-DLH/2026/01/PILAH" class="{{ $fieldClass }} w-full">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Keterangan / Catatan Peruntukan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan pengalokasian dana insentif..." class="{{ $fieldClass }} w-full">{{ old('notes') }}</textarea>
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
