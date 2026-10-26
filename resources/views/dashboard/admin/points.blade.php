<x-layouts::app :title="__('Pencairan Dana & Saldo Poin Warga - Admin DLH')">
    <div x-data="{ showAllocationModal: false }" class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Pencairan & Poin</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Sirkulasi Poin & Anggaran Kas Reward</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Monitoring perolehan reward pilah sampah, kontrol kas APBD/daur ulang, dan persetujuan pencairan poin warga.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-bold">
                    Konversi: 1 Poin = Rp {{ number_format($pointRate, 0, ',', '.') }}
                </span>
                <button type="button" @click="showAllocationModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    + Tambah Anggaran Kas
                </button>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-xs font-semibold shadow-sm transition print:hidden">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Laporan
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Kartu Keuangan & Poin -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Saldo Kas Tersedia (Highlight Utama) -->
            <div class="rounded-xl border border-emerald-300 dark:border-emerald-800 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/30 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">Saldo Kas Tersedia</span>
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-2">
                    Rp {{ number_format($stats['saldo_anggaran_tersedia'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-1">Siap dicairkan untuk klaim warga</p>
            </div>

            <!-- Total Anggaran Masuk -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Kas Masuk</span>
                <h3 class="text-2xl font-black text-teal-600 dark:text-teal-400 mt-2">
                    Rp {{ number_format($stats['total_anggaran_masuk'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-zinc-400 mt-1">APBD, Daur Ulang & CSR</p>
            </div>

            <!-- Total Dana Dicairkan -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Realisasi Dicairkan</span>
                <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-2">
                    Rp {{ number_format($stats['total_pencairan_sukses'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-zinc-400 mt-1">Klaim disetujui & ditransfer</p>
            </div>

            <!-- Permohonan Menunggu -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Menunggu Verifikasi</span>
                <h3 class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-2">
                    {{ $stats['pending_requests'] }} <span class="text-sm font-medium text-zinc-500">Klaim</span>
                </h3>
                <p class="text-xs text-zinc-400 mt-1">Nominal: Rp {{ number_format($stats['total_pending_amount'], 0, ',', '.') }}</p>
            </div>

            <!-- Saldo Poin Warga Beredar -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Poin Beredar (Liabilitas)</span>
                <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    {{ number_format($stats['total_poin_beredar'], 0, ',', '.') }} <span class="text-xs font-medium text-zinc-500">Pts</span>
                </h3>
                <p class="text-xs text-zinc-400 mt-1">≈ Rp {{ number_format($stats['total_poin_beredar'] * $pointRate, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Filter & Pencarian Laporan Transaksi -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs print:hidden">
            <!-- Filter Status Tab -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <a href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => ''])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ ! $currentStatus ? 'bg-emerald-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Semua ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Menunggu ({{ $statusCounts['pending'] }})
                </a>
                <a href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Disetujui ({{ $statusCounts['approved'] }})
                </a>
                <a href="{{ route('dashboard.admin.points', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'rejected' ? 'bg-red-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Ditolak ({{ $statusCounts['rejected'] }})
                </a>
            </div>

            <!-- Form Pencarian & Tanggal -->
            <form method="GET" action="{{ route('dashboard.admin.points') }}" class="flex items-center gap-2 flex-wrap">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-2.5 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs" title="Dari Tanggal">
                <span class="text-zinc-400">s/d</span>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="px-2.5 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs" title="Sampai Tanggal">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama warga..." class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs w-36 sm:w-44">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold transition">
                    Filter
                </button>
                @if ($search || $dateFrom || $dateTo || $currentStatus)
                    <a href="{{ route('dashboard.admin.points') }}" class="px-2 py-1.5 text-zinc-500 hover:text-zinc-700 transition" title="Reset Filter">✕</a>
                @endif
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tabel Permohonan Pencairan -->
            <div class="lg:col-span-2 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden text-xs">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Daftar Transaksi Penukaran Poin Warga</h2>
                        <p class="text-xs text-zinc-500">Laporan transaksi pencairan reward pilah sampah ke saldo rupiah / e-wallet.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 font-semibold uppercase">
                                <th class="py-3 px-4">Warga Pemohon</th>
                                <th class="py-3 px-4">Poin & Nilai Rupiah</th>
                                <th class="py-3 px-4">Tujuan Transfer</th>
                                <th class="py-3 px-4">Status & Verifikasi</th>
                                <th class="py-3 px-4 text-right print:hidden">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($withdrawals as $w)
                                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                                    <td class="py-3.5 px-4">
                                        <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $w->user->name ?? 'Warga' }}</p>
                                        <p class="text-zinc-400 text-[11px]">{{ $w->user->phone_number ?? '-' }}</p>
                                        <p class="text-zinc-400 text-[10px] mt-0.5">{{ $w->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ number_format($w->points_used ?? 0) }} Poin</span>
                                        <p class="font-black text-emerald-700 dark:text-emerald-300 text-xs">Rp {{ number_format($w->amount, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-semibold uppercase tracking-wider text-[11px] px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800">{{ str_replace('_', ' ', $w->destination_type) }}</span>
                                        <p class="font-mono text-zinc-700 dark:text-zinc-300 mt-1 font-semibold">{{ $w->destination_account ?? '-' }}</p>
                                        @if ($w->requester_note)
                                            <p class="text-zinc-400 text-[10px] italic mt-0.5">Catatan: {{ $w->requester_note }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $w->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' :
                                               ($w->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300') }}">
                                            {{ $w->status }}
                                        </span>
                                        @if ($w->status !== 'pending' && $w->reviewer)
                                            <p class="text-[10px] text-zinc-500 mt-1">
                                                Oleh: <strong class="text-zinc-700 dark:text-zinc-300">{{ $w->reviewer->name }}</strong>
                                            </p>
                                            <p class="text-[9px] text-zinc-400">{{ $w->reviewed_at ? $w->reviewed_at->translatedFormat('d M Y, H:i') : '' }}</p>
                                        @endif
                                        @if ($w->admin_note)
                                            <p class="text-[10px] text-zinc-500 italic mt-0.5">"{{ $w->admin_note }}"</p>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right print:hidden">
                                        @if ($w->status === 'pending')
                                            <form method="POST" action="{{ route('dashboard.admin.points.update', $w) }}" class="inline-flex flex-col sm:flex-row gap-1 justify-end">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="status" value="approved" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition text-[11px]" title="Setujui dan transfer dana">
                                                    ✓ Setujui
                                                </button>
                                                <button type="submit" name="status" value="rejected" class="px-2 py-1 rounded bg-red-600 hover:bg-red-700 text-white font-bold transition text-[11px]" title="Tolak dan kembalikan poin">
                                                    ✕ Tolak
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-zinc-400 text-[11px] italic">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-zinc-400 text-xs">
                                        Belum ada permohonan penukaran poin warga sesuai filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($withdrawals->hasPages())
                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 print:hidden">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            </div>

            <!-- Kolom Kanan: Top Warga & Alokasi Anggaran Terakhir -->
            <div class="space-y-6">
                <!-- Top Warga Teraktif -->
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm text-xs">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-1">Top Warga Peduli Lingkungan</h3>
                    <p class="text-zinc-500 mb-4">Warga Kota Madiun dengan saldo poin & setoran pilah sampah terbanyak.</p>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($topWarga as $idx => $wp)
                            <div class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="size-6 rounded-full flex items-center justify-center font-bold text-xs {{ $idx === 0 ? 'bg-amber-400 text-white' : ($idx === 1 ? 'bg-zinc-300 text-zinc-800' : ($idx === 2 ? 'bg-amber-700 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500')) }}">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $wp->user->name ?? 'Warga' }}</p>
                                        <p class="text-zinc-400 text-[11px]">{{ $wp->kelurahan ?? 'Kota Madiun' }}, {{ $wp->kecamatan }}</p>
                                    </div>
                                </div>
                                <span class="font-black text-amber-600 dark:text-amber-400">
                                    {{ number_format($wp->saldo_poin) }} Pts
                                </span>
                            </div>
                        @empty
                            <div class="py-6 text-center text-zinc-400">Belum ada data warga terdaftar.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Info Sirkulasi Ekonomi Sampah -->
                <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/30 p-5 shadow-sm text-xs space-y-2">
                    <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300 font-bold">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Siklus Ekonomi Sirkular Madiun</span>
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300 text-[11px] leading-relaxed">
                        Pemberian reward poin kepada warga didukung oleh pendapatan hasil olahan kompos dan daur ulang TPS3R serta pos insentif APBD DLH untuk pengurangan timbulan sampah ke TPA Winongo.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Pemasukan & Alokasi Anggaran Kas Masuk -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden text-xs">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Riwayat Alokasi & Pemasukan Anggaran Kas Reward</h2>
                    <p class="text-xs text-zinc-500">Pencatatan pos dana masuk (APBD, Penjualan Kompos TPS3R, CSR) sebagai sumber kas pencairan poin warga.</p>
                </div>
                <button type="button" @click="showAllocationModal = true" class="self-start sm:self-auto px-3 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold transition text-xs print:hidden">
                    + Input Pemasukan Kas
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 font-semibold uppercase">
                            <th class="py-3 px-4">Tanggal & TA</th>
                            <th class="py-3 px-4">Sumber Anggaran</th>
                            <th class="py-3 px-4">No. Referensi / SK DPA</th>
                            <th class="py-3 px-4">Nominal Masuk</th>
                            <th class="py-3 px-4">Dicatat Oleh</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($allocations as $alloc)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $alloc->allocated_at->translatedFormat('d M Y') }}</p>
                                    <p class="text-zinc-400 text-[11px]">TA {{ $alloc->fiscal_year }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold
                                        {{ $alloc->source_type === 'apbd' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' :
                                           ($alloc->source_type === 'penjualan_daur_ulang' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300') }}">
                                        {{ $alloc->source_label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-zinc-700 dark:text-zinc-300">
                                    {{ $alloc->reference_number ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-black text-emerald-700 dark:text-emerald-300 text-sm">
                                    Rp {{ number_format($alloc->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400">
                                    {{ $alloc->creator->name ?? 'Admin DLH' }}
                                </td>
                                <td class="py-3.5 px-4 text-zinc-500 text-[11px]">
                                    {{ $alloc->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-zinc-400 text-xs">
                                    Belum ada catatan alokasi anggaran kas reward. Silakan tambah alokasi baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Dialog: Tambah Alokasi Anggaran Kas Masuk -->
        <div x-show="showAllocationModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm print:hidden"
             @keydown.escape.window="showAllocationModal = false">
            <div @click.away="showAllocationModal = false"
                 class="w-full max-w-lg rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 p-6 shadow-2xl space-y-4 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Tambah Alokasi Anggaran Kas Reward</h3>
                        <p class="text-xs text-zinc-500">Catat sumber dana kas masuk untuk menjamin ketersediaan pencairan poin warga.</p>
                    </div>
                    <button type="button" @click="showAllocationModal = false" class="size-7 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-zinc-600">✕</button>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.points.allocations.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Sumber Anggaran <span class="text-red-500">*</span></label>
                        <select name="source_type" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs">
                            <option value="apbd">Alokasi APBD Kota Madiun</option>
                            <option value="penjualan_daur_ulang">Hasil Penjualan Kompos & Daur Ulang TPS3R</option>
                            <option value="csr_kemitraan">Kemitraan Swasta / CSR / EPR</option>
                            <option value="retribusi">Penerimaan Retribusi Persampahan</option>
                            <option value="lainnya">Lain-lain</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nominal Anggaran (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" min="1000" step="1000" name="amount" placeholder="misal: 25000000" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tahun Anggaran (TA) <span class="text-red-500">*</span></label>
                            <input type="number" min="2020" max="2099" name="fiscal_year" value="{{ date('Y') }}" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Alokasi <span class="text-red-500">*</span></label>
                            <input type="date" name="allocated_at" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">No. Referensi / SK DPA</label>
                            <input type="text" name="reference_number" placeholder="DPA-DLH/2026/01/PILAH" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Keterangan / Catatan Peruntukan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan pengalokasian dana insentif..." class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" @click="showAllocationModal = false" class="px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                            Simpan Alokasi Anggaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
