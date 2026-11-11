<x-layouts::app :title="__('Retribusi Pelayanan Persampahan - Admin DLH')">
    @php
        $fieldClass = 'min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div x-data="{ showGenerateModal: false, showSettingsModal: false, showReminderModal: false }" class="civic-page">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Retribusi Sampah"
            eyebrow="Keuangan & Kebijakan Retribusi"
            title="Retribusi Pelayanan Persampahan (TRK-F-013)"
            description="Pengelolaan tagihan bulanan warga, aturan potongan poin bank sampah, pengingat WhatsApp & siaran massal, serta proteksi penjemputan."
            icon="banknotes"
        >
            <x-slot:actions>
                <button type="button" @click="showSettingsModal = true" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                    <flux:icon.cog-6-tooth class="size-4" />
                    Pengaturan Tarif & Kebijakan
                </button>
                <button type="button" @click="showReminderModal = true" class="inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-amber-700">
                    <flux:icon.megaphone class="size-4" />
                    Kirim Pengingat Massal
                </button>
                <button type="button" @click="showGenerateModal = true" class="civic-button-primary !min-h-10 !py-2 !text-xs">
                    <flux:icon.plus class="size-4" />
                    + Terbitkan Tagihan Massal
                </button>
                <button type="button" onclick="window.print()" class="civic-button-secondary !min-h-10 !py-2 !text-xs print:hidden">
                    <flux:icon.printer class="size-4" />
                    Cetak
                </button>
            </x-slot:actions>
        </x-civic.subpage-header>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="civic-alert-danger" role="alert">
                <span>{{ session('error') }}</span>
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

        <!-- Banner Kebijakan Aktif Retribusi -->
        <div class="civic-card flex flex-col justify-between gap-3 border-civic-200 bg-civic-50/70 p-4 text-xs md:flex-row md:items-center dark:border-civic-900 dark:bg-civic-950/30">
            <div class="flex items-center gap-3">
                <span class="civic-icon-tile font-mono font-extrabold">Rp</span>
                <div>
                    <span class="font-bold text-slate-950 dark:text-white">Tarif Bulanan Saat Ini: Rp {{ number_format($retributionConfig['monthly_tariff'] ?? 10000, 0, ',', '.') }} / KK</span>
                    <span class="ml-1 text-slate-500 dark:text-slate-400">· Jatuh Tempo Standar: Tgl {{ $retributionConfig['standard_due_day'] ?? 20 }}</span>
                    <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-300">
                        Maks. Diskon Poin: <strong class="text-civic-700 dark:text-civic-300">{{ $retributionConfig['max_point_discount_percent'] ?? 100 }}%</strong>
                        · Kurs Poin: <strong>Rp {{ number_format($retributionConfig['point_conversion_rate'] ?? 10, 0, ',', '.') }} / Poin</strong>
                        · Masa Tenggang: <strong>{{ $retributionConfig['grace_period_days'] ?? 0 }} Hari</strong>
                    </p>
                </div>
            </div>
            <button type="button" @click="showSettingsModal = true" class="civic-link self-start md:self-auto">
                Ubah Kebijakan & Tarif →
            </button>
        </div>

        <!-- KPI Kartu Retribusi -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Tagihan Diterbitkan</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-slate-950 dark:text-white">
                    Rp {{ number_format($stats['total_tagihan_rp'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $stats['total_bills'] }} data tagihan terbit</p>
            </div>

            <div class="civic-hero !rounded-2xl !p-5">
                <div class="civic-hero-grid" aria-hidden="true"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-civic-200">Realisasi Lunas</span>
                    <span class="size-2 rounded-full bg-emerald-300"></span>
                </div>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-white">
                    Rp {{ number_format($stats['total_lunas_rp'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-civic-100/80">{{ $stats['lunas_count'] }} tagihan lunas</p>
            </div>

            <div class="civic-stat-card border-amber-200/80 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/20">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Total Tunggakan</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($stats['total_tunggakan_rp'], 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-amber-800/80 dark:text-amber-300/80">{{ $stats['belum_dibayar_count'] }} menunggu pembayaran</p>
            </div>

            <div class="civic-stat-card border-red-200/80 bg-red-50/40 dark:border-red-900/40 dark:bg-red-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-red-700 dark:text-red-300">Penjemputan Ditangguhkan</span>
                    <span class="size-2 animate-pulse rounded-full bg-red-500"></span>
                </div>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-red-600 dark:text-red-400">
                    {{ $stats['overdue_count'] }} Warga
                </h3>
                <p class="mt-1 text-xs text-red-700/80 dark:text-red-300/80">Melewati batas jatuh tempo</p>
            </div>
        </div>

        <!-- Quick Filter Tabs -->
        <div id="retributionsStatusTabs" data-ajax-targets="#retributionsStatusTabs,#retributionsTableCard" class="civic-filter-rail w-full justify-start">
            <a
                href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => null])) }}"
                data-ajax-link
                class="civic-filter-chip !text-xs {{ empty($statusFilter) ? 'civic-filter-chip-active' : '' }}"
            >
                Semua Tagihan ({{ $stats['total_bills'] }})
            </a>
            <a
                href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => 'belum_dibayar'])) }}"
                data-ajax-link
                class="civic-filter-chip inline-flex items-center gap-1.5 !text-xs {{ $statusFilter === 'belum_dibayar' ? 'civic-filter-chip-active' : '' }}"
            >
                <flux:icon.clock class="size-3.5" />
                Menunggu Pembayaran ({{ $stats['belum_dibayar_count'] }})
            </a>
            <a
                href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => 'overdue'])) }}"
                data-ajax-link
                class="civic-filter-chip inline-flex items-center gap-1.5 !text-xs {{ $statusFilter === 'overdue' ? 'civic-filter-chip-active !text-red-700 dark:!text-red-300' : '' }}"
            >
                <flux:icon.exclamation-triangle class="size-3.5" />
                Menunggak / Terkunci ({{ $stats['overdue_count'] }})
            </a>
            <a
                href="{{ route('dashboard.admin.retributions', array_merge(request()->except(['status', 'page']), ['status' => 'lunas'])) }}"
                data-ajax-link
                class="civic-filter-chip inline-flex items-center gap-1.5 !text-xs {{ $statusFilter === 'lunas' ? 'civic-filter-chip-active' : '' }}"
            >
                <flux:icon.check class="size-3.5" />
                Lunas ({{ $stats['lunas_count'] }})
            </a>
        </div>

        <!-- Filter & Search Section -->
        @php
            $retributionStatusOptions = [
                '' => 'Semua Status',
                'lunas' => 'Lunas',
                'belum_dibayar' => 'Menunggu Bayar (Aktif)',
                'overdue' => 'Lewat Jatuh Tempo (Ditangguhkan)',
            ];
            $retributionMonthOptions = ['' => 'Semua Bulan'];
            for ($m = 1; $m <= 12; $m++) {
                $retributionMonthOptions[(string) $m] = DateTime::createFromFormat('!m', $m)->format('F');
            }
        @endphp
        <div class="civic-card p-4">
            <form id="retributionsFilterForm" method="GET" action="{{ route('dashboard.admin.retributions') }}" data-ajax-form data-ajax-targets="#retributionsStatusTabs,#retributionsTableCard" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <span class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Status Pembayaran</span>
                    <x-public.select-filter
                        name="status"
                        :value="$statusFilter ?? ''"
                        :options="$retributionStatusOptions"
                        placeholder="Semua Status"
                        heightClass="h-10 text-xs"
                    />
                </div>
                <div>
                    <span class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Bulan</span>
                    <x-public.select-filter
                        name="month"
                        :value="(string) ($currentMonth ?? '')"
                        :options="$retributionMonthOptions"
                        placeholder="Semua Bulan"
                        heightClass="h-10 text-xs"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Tahun</label>
                    <input type="number" name="year" value="{{ $currentYear ?? 2026 }}" placeholder="Tahun" class="{{ $fieldClass }}" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Pencarian Warga / No. Tagihan</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Nama, email, atau nomor tagihan..." class="{{ $fieldClass }}" />
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="civic-button-primary flex-1 !min-h-10 !py-2 !text-xs">
                        Filter Data
                    </button>
                    <a href="{{ route('dashboard.admin.retributions') }}" data-ajax-link data-ajax-targets="#retributionsStatusTabs,#retributionsTableCard" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Tagihan Retribusi -->
        <div id="retributionsTableCard" data-ajax-zone="admin-retributions-table" data-ajax-targets="#retributionsStatusTabs,#retributionsTableCard" class="civic-card overflow-hidden">
            <div class="civic-table-wrap rounded-none border-0">
                <table class="civic-table text-xs">
                    <thead>
                        <tr>
                            <th>No. Tagihan & Periode</th>
                            <th>Warga Wajib Retribusi</th>
                            <th>Nominal Tagihan</th>
                            <th>Potongan Poin</th>
                            <th>Dibayar (Gateway/Kas)</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th class="text-right print:hidden">Aksi & Pengingat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bills as $bill)
                            <tr>
                                <td>
                                    <span class="font-mono font-bold text-civic-700 dark:text-civic-300">{{ $bill->nomor_tagihan }}</span>
                                    <div class="mt-0.5 text-xs text-slate-500">{{ $bill->periode_label }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-950 dark:text-white">{{ $bill->warga->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $bill->warga->email }}</div>
                                    @if ($bill->warga->phone_number)
                                        <div class="inline-flex items-center gap-1 text-xs text-slate-400"><flux:icon.phone class="size-3" /> {{ $bill->warga->phone_number }}</div>
                                    @endif
                                </td>
                                <td class="font-bold text-slate-950 dark:text-white">
                                    Rp {{ number_format($bill->nominal_tagihan, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if ($bill->poin_digunakan > 0)
                                        <span class="font-bold text-civic-700 dark:text-civic-300">
                                            -{{ number_format($bill->poin_digunakan) }} Poin
                                        </span>
                                        <div class="text-xs text-slate-400">(Rp {{ number_format($bill->nominal_potongan_poin, 0, ',', '.') }})</div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($bill->nominal_dibayar > 0)
                                        <div class="font-bold text-slate-800 dark:text-slate-200">
                                            Rp {{ number_format($bill->nominal_dibayar, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    <span class="mt-0.5 inline-block text-xs font-bold uppercase text-slate-500">
                                        {{ str_replace('_', ' ', $bill->metode_pembayaran) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="{{ $bill->isOverdue() ? 'font-bold text-red-600' : 'text-slate-600 dark:text-slate-400' }}">
                                        {{ $bill->jatuh_tempo->format('d M Y') }}
                                    </div>
                                    @if ($bill->isOverdue())
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-red-700 dark:text-red-400">
                                            <flux:icon.exclamation-triangle class="size-3" />
                                            Akses Ditangguhkan
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($bill->status === 'lunas')
                                        <span class="civic-status-success">✓ Lunas</span>
                                        @if ($bill->paid_at)
                                            <div class="mt-0.5 text-xs text-slate-400">{{ $bill->paid_at->format('d/m/y H:i') }}</div>
                                        @endif
                                    @elseif ($bill->isOverdue())
                                        <span class="civic-status-danger">Terlambat</span>
                                    @else
                                        <span class="civic-status-warning">Belum Bayar</span>
                                    @endif
                                </td>
                                <td class="text-right print:hidden">
                                    @if ($bill->status !== 'lunas')
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($bill->whats_app_url)
                                                <a href="{{ $bill->whats_app_url }}" target="_blank" rel="noopener noreferrer" title="Kirim Pengingat WhatsApp ke Warga" class="civic-button-secondary !min-h-8 !px-2.5 !py-1 !text-xs">
                                                    WA
                                                </a>
                                            @endif

                                            <form method="POST" action="{{ route('dashboard.admin.retributions.confirm-manual', $bill) }}" onsubmit="return confirm('Konfirmasi tagihan ini lunas secara manual (tunai di loket DLH)?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-xl bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-civic-100 hover:text-civic-800 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-civic-950/60">
                                                    Kas DLH
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">Tercatat</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-sm text-slate-400">
                                    Tidak ada data tagihan retribusi yang cocok dengan kriteria ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bills->hasPages())
                <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>

        <!-- Modal 1: Terbitkan Tagihan Massal -->
        <div x-show="showGenerateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div @click.away="showGenerateModal = false" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Terbitkan Tagihan Retribusi Massal</h3>
                    <button type="button" @click="showGenerateModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <p class="mb-4 text-xs text-slate-500">Sistem akan membuat tagihan retribusi kebersihan untuk seluruh akun warga aktif Kota Madiun yang belum memiliki tagihan pada periode terpilih.</p>

                <form method="POST" action="{{ route('dashboard.admin.retributions.generate') }}" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Periode Bulan</label>
                        <select name="periode_bulan" class="{{ $fieldClass }}" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Periode Tahun</label>
                        <input type="number" name="periode_tahun" value="{{ now()->year }}" class="{{ $fieldClass }}" required />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Nominal Iuran Retribusi (Rp)</label>
                        <input type="number" name="nominal_tagihan" value="{{ $retributionConfig['monthly_tariff'] ?? 10000 }}" step="500" class="{{ $fieldClass }}" required />
                        <span class="text-xs text-slate-400">Tarif otomatis mengambil nilai dari Master Data Kebijakan Retribusi.</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Batas Tanggal Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" value="{{ now()->setDay($retributionConfig['standard_due_day'] ?? 20)->toDateString() }}" class="{{ $fieldClass }}" required />
                        <span class="text-xs text-red-500">Setelah tanggal ini (+ masa tenggang), warga yang belum membayar akan otomatis diblokir dari penjadwalan jemput sampah.</span>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <button type="button" @click="showGenerateModal = false" class="civic-button-secondary !min-h-9 !py-1.5 !text-xs">
                            Batal
                        </button>
                        <button type="submit" class="civic-button-primary !min-h-9 !py-1.5 !text-xs">
                            Proses & Terbitkan Tagihan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2: Pengaturan Tarif & Kebijakan Retribusi -->
        <div x-show="showSettingsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div @click.away="showSettingsModal = false" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Pengaturan Tarif & Kebijakan Retribusi</h3>
                        <p class="text-xs text-slate-500">Konfigurasi parameter tagihan dan pesan pengingat resmi DLH Kota Madiun.</p>
                    </div>
                    <button type="button" @click="showSettingsModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.retributions.settings') }}" class="flex flex-col gap-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Tarif Bulanan Standar (Rp)</label>
                            <input type="number" name="monthly_tariff" value="{{ $retributionConfig['monthly_tariff'] ?? 10000 }}" min="0" step="500" class="{{ $fieldClass }}" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Tanggal Jatuh Tempo Bulanan</label>
                            <input type="number" name="standard_due_day" value="{{ $retributionConfig['standard_due_day'] ?? 20 }}" min="1" max="28" class="{{ $fieldClass }}" required />
                            <span class="text-xs text-slate-400">Tanggal 1 s/d 28 setiap bulannya.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Maks. Potongan Poin (%)</label>
                            <input type="number" name="max_point_discount_percent" value="{{ $retributionConfig['max_point_discount_percent'] ?? 100 }}" min="0" max="100" class="{{ $fieldClass }}" required />
                            <span class="text-xs text-slate-400">100% artinya poin bisa menutupi seluruh iuran.</span>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Masa Tenggang / Grace Period (Hari)</label>
                            <input type="number" name="grace_period_days" value="{{ $retributionConfig['grace_period_days'] ?? 0 }}" min="0" max="30" class="{{ $fieldClass }}" required />
                            <span class="text-xs text-slate-400">Toleransi hari sebelum kunci jemput sampah aktif.</span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Nilai Tukar Poin (IDR per 1 Poin Sampah)</label>
                        <input type="number" name="point_conversion_rate" value="{{ $retributionConfig['point_conversion_rate'] ?? 10 }}" min="1" step="1" class="{{ $fieldClass }}" required />
                        <span class="text-xs text-slate-400">Contoh: 10 artinya 1.000 poin bernilai Rp 10.000.</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Template Pesan Pengingat WhatsApp</label>
                        <textarea name="reminder_template" rows="4" class="{{ $fieldClass }} font-mono" required>{{ $retributionConfig['reminder_template'] ?? '' }}</textarea>
                        <div class="mt-1 space-y-0.5 text-xs text-slate-500">
                            <span class="font-bold">Tag variabel yang didukung:</span>
                            <div class="mt-1 flex flex-wrap gap-1 font-mono text-xs">
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-civic-700 dark:bg-slate-800">{nama}</span>
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-civic-700 dark:bg-slate-800">{periode}</span>
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-civic-700 dark:bg-slate-800">{nominal}</span>
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-civic-700 dark:bg-slate-800">{jatuh_tempo}</span>
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-civic-700 dark:bg-slate-800">{status_info}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <button type="button" @click="showSettingsModal = false" class="civic-button-secondary !min-h-9 !py-1.5 !text-xs">
                            Batal
                        </button>
                        <button type="submit" class="civic-button-primary !min-h-9 !py-1.5 !text-xs">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 3: Kirim Pengingat Massal (Broadcast) -->
        <div x-show="showReminderModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div @click.away="showReminderModal = false" class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Siarkan Pengingat Retribusi Massal</h3>
                        <p class="text-xs text-slate-500">Kirim notifikasi pengumuman resmi ke seluruh warga via aplikasi Madiun Terpadu.</p>
                    </div>
                    <button type="button" @click="showReminderModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.retributions.send-reminders') }}" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Target Sasaran Warga</label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-slate-200 p-2.5 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                                <input type="radio" name="target_filter" value="all_unpaid" checked class="mt-0.5 text-civic-700" />
                                <div>
                                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Semua Belum Bayar</span>
                                    <span class="block text-xs text-slate-400">{{ $stats['belum_dibayar_count'] + $stats['overdue_count'] }} tagihan aktif & overdue</span>
                                </div>
                            </label>
                            <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-red-200 bg-red-50/40 p-2.5 hover:bg-red-50 dark:border-red-800 dark:bg-red-950/20 dark:hover:bg-red-950/40">
                                <input type="radio" name="target_filter" value="overdue_only" class="mt-0.5 text-red-600" />
                                <div>
                                    <span class="block text-xs font-bold text-red-700 dark:text-red-300">Hanya Menunggak (Overdue)</span>
                                    <span class="block text-xs text-red-500">{{ $stats['overdue_count'] }} tagihan telah lewat jatuh tempo</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Judul Notifikasi Pengumuman</label>
                        <input type="text" name="title" value="Pemberitahuan Tagihan Retribusi Pelayanan Persampahan Kota Madiun" class="{{ $fieldClass }}" required />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Isi Pesan Notifikasi</label>
                        <textarea name="content" rows="4" class="{{ $fieldClass }}" required>Yth. Warga Kota Madiun, tagihan retribusi pelayanan kebersihan periode berjalan telah diterbitkan. Mohon segera melakukan pelunasan sebelum batas jatuh tempo agar layanan penjemputan sampah harian tetap aktif. Pembayaran dapat dilakukan langsung melalui aplikasi menggunakan Saldo Poin Bank Sampah TPS3R Anda atau via pembayaran online QRIS/E-Wallet.</textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Prioritas Siaran</label>
                        <select name="priority" class="{{ $fieldClass }}">
                            <option value="normal">Normal (Informasi Rutin)</option>
                            <option value="high" selected>Tinggi / High (Penting)</option>
                            <option value="urgent">Mendesak / Urgent (Peringatan Pemblokiran)</option>
                        </select>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <button type="button" @click="showReminderModal = false" class="civic-button-secondary !min-h-9 !py-1.5 !text-xs">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-xl bg-amber-600 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-amber-700">
                            Siarkan Pengingat Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
