<x-layouts::app :title="__('Retribusi Pelayanan Persampahan - Admin DLH')">
    <div x-data="{ showGenerateModal: false, showSettingsModal: false, showReminderModal: false }" class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Retribusi Sampah</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Retribusi Pelayanan Persampahan (TRK-F-013)</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Pengelolaan tagihan bulanan warga, aturan potongan poin bank sampah, pengingat WhatsApp & siaran massal, serta proteksi penjemputan.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="showSettingsModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-xs font-bold shadow-sm transition">
                    <svg class="size-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pengaturan Tarif & Kebijakan
                </button>
                <button type="button" @click="showReminderModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    Kirim Pengingat Massal
                </button>
                <button type="button" @click="showGenerateModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    + Terbitkan Tagihan Massal
                </button>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-xs font-semibold shadow-sm transition print:hidden">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                <span>{{ session('error') }}</span>
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

        <!-- Banner Kebijakan Aktif Retribusi -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-black">
                    Rp
                </span>
                <div>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">Tarif Bulanan Saat Ini: Rp {{ number_format($retributionConfig['monthly_tariff'] ?? 10000, 0, ',', '.') }} / KK</span>
                    <span class="text-zinc-500 ml-1">· Jatuh Tempo Standar: Tgl {{ $retributionConfig['standard_due_day'] ?? 20 }}</span>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Maks. Diskon Poin: <strong class="text-emerald-600">{{ $retributionConfig['max_point_discount_percent'] ?? 100 }}%</strong>
                        · Kurs Poin: <strong>Rp {{ number_format($retributionConfig['point_conversion_rate'] ?? 10, 0, ',', '.') }} / Poin</strong>
                        · Masa Tenggang: <strong>{{ $retributionConfig['grace_period_days'] ?? 0 }} Hari</strong>
                    </p>
                </div>
            </div>
            <button type="button" @click="showSettingsModal = true" class="text-emerald-600 dark:text-emerald-400 hover:underline font-bold text-xs self-start md:self-auto">
                Ubah Kebijakan & Tarif →
            </button>
        </div>

        <!-- KPI Kartu Retribusi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Target Retribusi -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Total Tagihan Diterbitkan</span>
                <h3 class="text-2xl font-black text-zinc-900 dark:text-zinc-100 mt-2">
                    Rp {{ number_format($stats['total_tagihan_rp'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-zinc-500 mt-1">{{ $stats['total_bills'] }} data tagihan terbit</p>
            </div>

            <!-- Realisasi Lunas -->
            <div class="rounded-xl border border-emerald-300 dark:border-emerald-800 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/30 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">Realisasi Lunas</span>
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                </div>
                <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-2">
                    Rp {{ number_format($stats['total_lunas_rp'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-1">{{ $stats['lunas_count'] }} tagihan lunas</p>
            </div>

            <!-- Total Tunggakan Belum Bayar -->
            <div class="rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/20 p-5 shadow-sm">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Total Tunggakan</span>
                <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    Rp {{ number_format($stats['total_tunggakan_rp'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-amber-700/80 dark:text-amber-400/80 mt-1">{{ $stats['belum_dibayar_count'] }} menunggu pembayaran</p>
            </div>

            <!-- Overdue & Terkena Penangguhan -->
            <div class="rounded-xl border border-rose-300 dark:border-rose-900/50 bg-rose-50/60 dark:bg-rose-950/30 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Penjemputan Ditangguhkan</span>
                    <span class="size-2 rounded-full bg-rose-500 animate-pulse"></span>
                </div>
                <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-2">
                    {{ $stats['overdue_count'] }} Warga
                </h3>
                <p class="text-xs text-rose-600/80 dark:text-rose-400/80 mt-1">Melewati batas jatuh tempo</p>
            </div>
        </div>

        <!-- Quick Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-zinc-200 dark:border-zinc-800">
            <a href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => null])) }}"
               class="px-3.5 py-2 rounded-lg text-xs font-bold transition whitespace-nowrap {{ empty($statusFilter) ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                Semua Tagihan ({{ $stats['total_bills'] }})
            </a>
            <a href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => 'belum_dibayar'])) }}"
               class="px-3.5 py-2 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'belum_dibayar' ? 'bg-amber-500 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                ⏳ Menunggu Pembayaran ({{ $stats['belum_dibayar_count'] }})
            </a>
            <a href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => 'overdue'])) }}"
               class="px-3.5 py-2 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'overdue' ? 'bg-rose-600 text-white shadow-sm' : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                ⚠️ Menunggak / Terkunci ({{ $stats['overdue_count'] }})
            </a>
            <a href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => 'lunas'])) }}"
               class="px-3.5 py-2 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'lunas' ? 'bg-emerald-700 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                ✓ Lunas ({{ $stats['lunas_count'] }})
            </a>
        </div>

        <!-- Filter & Search Section -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-sm">
            <form method="GET" action="{{ route('dashboard.admin.retributions') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Status Pembayaran</label>
                    <select name="status" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                        <option value="">Semua Status</option>
                        <option value="lunas" {{ $statusFilter === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="belum_dibayar" {{ $statusFilter === 'belum_dibayar' ? 'selected' : '' }}>Menunggu Bayar (Aktif)</option>
                        <option value="overdue" {{ $statusFilter === 'overdue' ? 'selected' : '' }}>Lewat Jatuh Tempo (Ditangguhkan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Bulan</label>
                    <select name="month" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                        <option value="">Semua Bulan</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Tahun</label>
                    <input type="number" name="year" value="{{ $currentYear ?? 2026 }}" placeholder="Tahun" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Pencarian Warga / No. Tagihan</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Nama, email, atau nomor tagihan..." class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" />
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                        Filter Data
                    </button>
                    <a href="{{ route('dashboard.admin.retributions') }}" class="px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 text-xs font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Tagihan Retribusi -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-4 py-3 font-bold">No. Tagihan & Periode</th>
                            <th class="px-4 py-3 font-bold">Warga Wajib Retribusi</th>
                            <th class="px-4 py-3 font-bold">Nominal Tagihan</th>
                            <th class="px-4 py-3 font-bold">Potongan Poin</th>
                            <th class="px-4 py-3 font-bold">Dibayar (Gateway/Kas)</th>
                            <th class="px-4 py-3 font-bold">Jatuh Tempo</th>
                            <th class="px-4 py-3 font-bold">Status</th>
                            <th class="px-4 py-3 font-bold text-right print:hidden">Aksi & Pengingat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($bills as $bill)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-4 py-3.5">
                                    <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $bill->nomor_tagihan }}</span>
                                    <div class="text-[11px] text-zinc-500 mt-0.5">{{ $bill->periode_label }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $bill->warga->name }}</div>
                                    <div class="text-[11px] text-zinc-500">{{ $bill->warga->email }}</div>
                                    @if ($bill->warga->phone_number)
                                        <div class="text-[11px] text-zinc-400">📞 {{ $bill->warga->phone_number }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-bold text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($bill->nominal_tagihan, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($bill->poin_digunakan > 0)
                                        <span class="text-emerald-700 dark:text-emerald-400 font-bold">
                                            -{{ number_format($bill->poin_digunakan) }} Poin
                                        </span>
                                        <div class="text-[11px] text-zinc-400">(Rp {{ number_format($bill->nominal_potongan_poin, 0, ',', '.') }})</div>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($bill->nominal_dibayar > 0)
                                        <div class="font-bold text-zinc-800 dark:text-zinc-200">
                                            Rp {{ number_format($bill->nominal_dibayar, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    <span class="inline-block mt-0.5 text-[10px] uppercase font-bold text-zinc-500">
                                        {{ str_replace('_', ' ', $bill->metode_pembayaran) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="{{ $bill->isOverdue() ? 'text-rose-600 font-bold' : 'text-zinc-600 dark:text-zinc-400' }}">
                                        {{ $bill->jatuh_tempo->format('d M Y') }}
                                    </div>
                                    @if ($bill->isOverdue())
                                        <span class="inline-flex items-center gap-1 text-[10px] text-rose-700 dark:text-rose-400 font-bold">
                                            ⚠️ Akses Ditangguhkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($bill->status === 'lunas')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            ✓ Lunas
                                        </span>
                                        @if ($bill->paid_at)
                                            <div class="text-[10px] text-zinc-400 mt-0.5">{{ $bill->paid_at->format('d/m/y H:i') }}</div>
                                        @endif
                                    @elseif ($bill->isOverdue())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Belum Bayar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right print:hidden">
                                    @if ($bill->status !== 'lunas')
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($bill->whats_app_url)
                                                <a href="{{ $bill->whats_app_url }}" target="_blank" rel="noopener noreferrer" title="Kirim Pengingat WhatsApp ke Warga" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold transition border border-emerald-200 dark:border-emerald-800">
                                                    <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                    WA
                                                </a>
                                            @endif

                                            <form method="POST" action="{{ route('dashboard.admin.retributions.confirm-manual', $bill) }}" onsubmit="return confirm('Konfirmasi tagihan ini lunas secara manual (tunai di loket DLH)?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-2.5 py-1 rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-100 dark:hover:bg-emerald-950/60 text-zinc-700 dark:text-zinc-200 hover:text-emerald-700 text-[11px] font-bold transition">
                                                    Kas DLH
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-zinc-400 text-[11px]">Tercatat</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-zinc-400">
                                    Tidak ada data tagihan retribusi yang cocok dengan kriteria ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bills->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>

        <!-- Modal 1: Terbitkan Tagihan Massal -->
        <div x-show="showGenerateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="showGenerateModal = false" class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-black text-zinc-900 dark:text-zinc-100">Terbitkan Tagihan Retribusi Massal</h3>
                    <button type="button" @click="showGenerateModal = false" class="text-zinc-400 hover:text-zinc-600">✕</button>
                </div>
                <p class="text-xs text-zinc-500 mb-4">Sistem akan membuat tagihan retribusi kebersihan untuk seluruh akun warga aktif Kota Madiun yang belum memiliki tagihan pada periode terpilih.</p>

                <form method="POST" action="{{ route('dashboard.admin.retributions.generate') }}" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Periode Bulan</label>
                        <select name="periode_bulan" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Periode Tahun</label>
                        <input type="number" name="periode_tahun" value="{{ now()->year }}" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800" required />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nominal Iuran Retribusi (Rp)</label>
                        <input type="number" name="nominal_tagihan" value="{{ $retributionConfig['monthly_tariff'] ?? 10000 }}" step="500" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800" required />
                        <span class="text-[10px] text-zinc-400">Tarif otomatis mengambil nilai dari Master Data Kebijakan Retribusi.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Batas Tanggal Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" value="{{ now()->setDay($retributionConfig['standard_due_day'] ?? 20)->toDateString() }}" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800" required />
                        <span class="text-[10px] text-rose-500">Setelah tanggal ini (+ masa tenggang), warga yang belum membayar akan otomatis diblokir dari penjadwalan jemput sampah.</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="button" @click="showGenerateModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            Proses & Terbitkan Tagihan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2: Pengaturan Tarif & Kebijakan Retribusi -->
        <div x-show="showSettingsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="showSettingsModal = false" class="bg-white dark:bg-zinc-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-zinc-200 dark:border-zinc-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-black text-zinc-900 dark:text-zinc-100">Pengaturan Tarif & Kebijakan Retribusi</h3>
                        <p class="text-xs text-zinc-500">Konfigurasi parameter tagihan dan pesan pengingat resmi DLH Kota Madiun.</p>
                    </div>
                    <button type="button" @click="showSettingsModal = false" class="text-zinc-400 hover:text-zinc-600">✕</button>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.retributions.settings') }}" class="flex flex-col gap-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tarif Bulanan Standar (Rp)</label>
                            <input type="number" name="monthly_tariff" value="{{ $retributionConfig['monthly_tariff'] ?? 10000 }}" min="0" step="500" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Jatuh Tempo Bulanan</label>
                            <input type="number" name="standard_due_day" value="{{ $retributionConfig['standard_due_day'] ?? 20 }}" min="1" max="28" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" required />
                            <span class="text-[10px] text-zinc-400">Tanggal 1 s/d 28 setiap bulannya.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Maks. Potongan Poin (%)</label>
                            <input type="number" name="max_point_discount_percent" value="{{ $retributionConfig['max_point_discount_percent'] ?? 100 }}" min="0" max="100" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" required />
                            <span class="text-[10px] text-zinc-400">100% artinya poin bisa menutupi seluruh iuran.</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Masa Tenggang / Grace Period (Hari)</label>
                            <input type="number" name="grace_period_days" value="{{ $retributionConfig['grace_period_days'] ?? 0 }}" min="0" max="30" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" required />
                            <span class="text-[10px] text-zinc-400">Toleransi hari sebelum kunci jemput sampah aktif.</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nilai Tukar Poin (IDR per 1 Poin Sampah)</label>
                        <input type="number" name="point_conversion_rate" value="{{ $retributionConfig['point_conversion_rate'] ?? 10 }}" min="1" step="1" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" required />
                        <span class="text-[10px] text-zinc-400">Contoh: 10 artinya 1.000 poin bernilai Rp 10.000.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Template Pesan Pengingat WhatsApp</label>
                        <textarea name="reminder_template" rows="4" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 font-mono" required>{{ $retributionConfig['reminder_template'] ?? '' }}</textarea>
                        <div class="text-[10px] text-zinc-500 mt-1 space-y-0.5">
                            <span class="font-bold">Tag variabel yang didukung:</span>
                            <div class="flex flex-wrap gap-1 mt-1 font-mono text-[9px]">
                                <span class="bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded text-emerald-600">{nama}</span>
                                <span class="bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded text-emerald-600">{periode}</span>
                                <span class="bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded text-emerald-600">{nominal}</span>
                                <span class="bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded text-emerald-600">{jatuh_tempo}</span>
                                <span class="bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded text-emerald-600">{status_info}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="button" @click="showSettingsModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 3: Kirim Pengingat Massal (Broadcast) -->
        <div x-show="showReminderModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="showReminderModal = false" class="bg-white dark:bg-zinc-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-black text-zinc-900 dark:text-zinc-100">Siarkan Pengingat Retribusi Massal</h3>
                        <p class="text-xs text-zinc-500">Kirim notifikasi pengumuman resmi ke seluruh warga via aplikasi Madiun Terpadu.</p>
                    </div>
                    <button type="button" @click="showReminderModal = false" class="text-zinc-400 hover:text-zinc-600">✕</button>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.retributions.send-reminders') }}" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Target Sasaran Warga</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label class="flex items-start gap-2 p-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <input type="radio" name="target_filter" value="all_unpaid" checked class="mt-0.5 text-emerald-600" />
                                <div>
                                    <span class="block text-xs font-bold text-zinc-800 dark:text-zinc-200">Semua Belum Bayar</span>
                                    <span class="block text-[10px] text-zinc-400">{{ $stats['belum_dibayar_count'] + $stats['overdue_count'] }} tagihan aktif & overdue</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2 p-2.5 rounded-lg border border-rose-300 dark:border-rose-800 cursor-pointer bg-rose-50/40 dark:bg-rose-950/20 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                                <input type="radio" name="target_filter" value="overdue_only" class="mt-0.5 text-rose-600" />
                                <div>
                                    <span class="block text-xs font-bold text-rose-700 dark:text-rose-300">Hanya Menunggak (Overdue)</span>
                                    <span class="block text-[10px] text-rose-500">{{ $stats['overdue_count'] }} tagihan telah lewat jatuh tempo</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Judul Notifikasi Pengumuman</label>
                        <input type="text" name="title" value="Pemberitahuan Tagihan Retribusi Pelayanan Persampahan Kota Madiun" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" required />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Isi Pesan Notifikasi</label>
                        <textarea name="content" rows="4" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200" required>Yth. Warga Kota Madiun, tagihan retribusi pelayanan kebersihan periode berjalan telah diterbitkan. Mohon segera melakukan pelunasan sebelum batas jatuh tempo agar layanan penjemputan sampah harian tetap aktif. Pembayaran dapat dilakukan langsung melalui aplikasi menggunakan Saldo Poin Bank Sampah TPS3R Anda atau via pembayaran online QRIS/E-Wallet.</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Prioritas Siaran</label>
                        <select name="priority" class="w-full text-xs rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                            <option value="normal">Normal (Informasi Rutin)</option>
                            <option value="high" selected>Tinggi / High (Penting)</option>
                            <option value="urgent">Mendesak / Urgent (Peringatan Pemblokiran)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="button" @click="showReminderModal = false" class="px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition">
                            Siarkan Pengingat Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
