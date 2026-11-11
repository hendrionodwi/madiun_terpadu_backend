<x-layouts::app :title="__('Riwayat & Status Verifikasi Laporan Lingkungan')">
    <div class="civic-page flex flex-col gap-6 p-2 md:p-4">
        <!-- Header Page -->
        <div class="civic-hero flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div class="civic-hero-grid"></div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="civic-kicker">
                        Workspace Pemrakarsa
                    </span>
                </div>
                <h1 class="civic-display mt-3">
                    Laporan Lingkungan
                </h1>
                <p class="mt-2 text-sm text-civic-100">
                    Susun draft, kirim dokumen, dan pantau hasil evaluasi DLH.
                </p>
            </div>
            <div>
                <a
                    href="{{ route('dashboard.pemrakarsa.reports.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-civic-900 shadow-sm transition hover:bg-civic-50"
                >
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Unggah Laporan Baru
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Filter Tabs & Pencarian -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Filter Status Tabs -->
            <div id="pemrakarsaReportsTabs" data-ajax-targets="#pemrakarsaReportsTabs,#pemrakarsaReportsTableCard" class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                @php
                    $filters = [
                        'all' => ['label' => 'Semua Dokumen', 'count' => $reportCounts['all']],
                        'draft' => ['label' => 'Draft', 'count' => $reportCounts['draft']],
                        'menunggu_verifikasi' => ['label' => 'Menunggu Review', 'count' => $reportCounts['menunggu_verifikasi']],
                        'disetujui' => ['label' => 'Disetujui DLH', 'count' => $reportCounts['disetujui']],
                        'perlu_revisi' => ['label' => 'Perlu Revisi', 'count' => $reportCounts['perlu_revisi']],
                    ];
                @endphp

                @foreach ($filters as $key => $f)
                    <a
                        href="{{ route('dashboard.pemrakarsa.reports', array_merge(request()->except('page'), ['status' => $key])) }}"
                        data-ajax-link
                        class="shrink-0 rounded-xl px-3.5 py-2 font-semibold transition {{ $currentStatus === $key ? 'bg-civic-700 text-white shadow-sm' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}"
                    >
                        {{ $f['label'] }} ({{ $f['count'] }})
                    </a>
                @endforeach
            </div>

            <!-- Filter Jenis Dokumen & Search Form -->
            <form id="pemrakarsaReportsFilterForm" method="GET" action="{{ route('dashboard.pemrakarsa.reports') }}" data-ajax-form data-ajax-targets="#pemrakarsaReportsTabs,#pemrakarsaReportsTableCard" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <div class="w-40">
                    <x-public.select-filter
                        name="jenis_dokumen"
                        :value="$currentJenis"
                        :options="[
                            'all' => 'Semua Izin',
                            'SPPL' => 'SPPL',
                            'UKL-UPL' => 'UKL-UPL',
                            'AMDAL' => 'AMDAL',
                        ]"
                        placeholder="Semua Izin"
                        heightClass="h-9 text-xs"
                    />
                </div>

                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari judul laporan..."
                        class="h-9 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs px-3 text-zinc-700 dark:text-zinc-200 w-48 sm:w-56"
                    />
                </div>

                <button type="submit" class="h-9 px-3.5 rounded-xl bg-zinc-800 text-white text-xs font-semibold hover:bg-zinc-700">
                    Cari
                </button>
            </form>
        </div>

        <!-- Tabel Laporan -->
        <div id="pemrakarsaReportsTableCard" data-ajax-zone="pemrakarsa-reports-table" data-ajax-targets="#pemrakarsaReportsTabs,#pemrakarsaReportsTableCard" class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[11px] font-bold border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="p-3.5">Judul Dokumen</th>
                            <th class="p-3.5">Periode Lapor</th>
                            <th class="p-3.5">Ringkasan Monitoring</th>
                            <th class="p-3.5">Status Validasi DLH</th>
                            <th class="p-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($reports as $lap)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50 transition">
                                <td class="p-3.5">
                                    <p class="font-bold text-sm text-zinc-900 dark:text-zinc-100">{{ $lap->judul_laporan }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-block text-[10px] px-2 py-0.5 rounded font-mono font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                            {{ $lap->jenis_dokumen }}
                                        </span>
                                        @if ($lap->metode_pengelolaan)
                                            <span class="text-[11px] text-zinc-500 truncate max-w-xs" title="{{ $lap->metode_pengelolaan }}">
                                                {{ Str::limit($lap->metode_pengelolaan, 40) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 font-mono text-[10px] text-zinc-500">
                                        <span>{{ $lap->nomor_laporan ?: 'Nomor dibuat saat pengajuan' }}</span>
                                        @if ($lap->nomor_bap)
                                            <span class="font-bold text-civic-700">BAP {{ $lap->nomor_bap }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-[11px] text-zinc-400">Dibuat {{ $lap->created_at->translatedFormat('d M Y, H:i') }}</p>
                                </td>
                                <td class="p-3.5 text-zinc-700 dark:text-zinc-300 font-medium">
                                    {{ $lap->periode_semester }} <br>
                                    <span class="text-xs text-zinc-500">Tahun {{ $lap->tahun }}</span>
                                </td>
                                <td class="p-3.5 text-zinc-700 dark:text-zinc-300">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $lap->timbulan_sampah_kg ? number_format($lap->timbulan_sampah_kg, 1, ',', '.') . ' kg/bulan' : 'Belum diisi' }}</p>
                                    @if (filled($lap->monitoring_data))
                                        <ul class="mt-1 space-y-0.5 text-[11px] text-zinc-500">
                                            @foreach (collect($lap->monitoring_data)->filter()->take(3) as $monitoringLabel => $monitoringValue)
                                                <li><span class="font-semibold">{{ str($monitoringLabel)->replace('_', ' ')->title() }}:</span> {{ $monitoringValue }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    @if ($lap->status === 'draft')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                            <span class="size-1.5 rounded-full bg-zinc-500"></span> Draft
                                        </span>
                                    @elseif ($lap->status === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                            <span class="size-1.5 rounded-full bg-amber-500"></span> Menunggu Review
                                        </span>
                                    @elseif ($lap->status === 'disetujui')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span> Disetujui DLH
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300">
                                            <span class="size-1.5 rounded-full bg-red-500"></span> Perlu Revisi
                                        </span>
                                    @endif

                                    @if ($lap->catatan_verifikasi)
                                        <div class="mt-1.5 p-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-[11px] text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                            <strong class="text-zinc-800 dark:text-zinc-200">Catatan DLH:</strong> "{{ $lap->catatan_verifikasi }}"
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    @if ($lap->file_path)
                                        <a href="{{ asset('storage/' . $lap->file_path) }}" target="_blank" class="civic-button-secondary px-3 py-1.5 text-xs">
                                            <flux:icon.arrow-down-tray class="size-4" />
                                            Berkas
                                        </a>
                                    @else
                                        <span class="text-[11px] text-zinc-400">Belum ada berkas</span>
                                    @endif
                                </td>
                            </tr>
                            @if (in_array($lap->status, ['draft', 'perlu_revisi'], true))
                                <tr class="bg-zinc-50/70 dark:bg-zinc-950/30">
                                    <td colspan="5" class="p-3.5">
                                        <details class="group">
                                            <summary class="civic-link cursor-pointer list-none">
                                                <flux:icon.pencil-square class="size-4" />
                                                {{ $lap->status === 'draft' ? 'Lanjutkan draft' : 'Perbaiki laporan' }}
                                                <flux:icon.chevron-down class="size-4 transition group-open:rotate-180" />
                                            </summary>
                                            <form method="POST" action="{{ route('dashboard.pemrakarsa.reports.update', $lap) }}" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-white p-4 text-xs sm:grid-cols-2 lg:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-900">
                                                @csrf
                                                @method('PATCH')
                                                <label class="space-y-1 font-semibold text-zinc-700 sm:col-span-2 dark:text-zinc-300">
                                                    <span>Judul laporan</span>
                                                    <input name="judul_laporan" value="{{ $lap->judul_laporan }}" required class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 font-normal dark:border-zinc-700 dark:bg-zinc-800">
                                                </label>
                                                <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                                    <span>Semester</span>
                                                    <select name="periode_semester" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 font-normal dark:border-zinc-700 dark:bg-zinc-800">
                                                        <option @selected($lap->periode_semester === 'Semester 1 (Jan - Jun)')>Semester 1 (Jan - Jun)</option>
                                                        <option @selected($lap->periode_semester === 'Semester 2 (Jul - Des)')>Semester 2 (Jul - Des)</option>
                                                    </select>
                                                </label>
                                                <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                                    <span>Tahun</span>
                                                    <input type="number" name="tahun" min="2020" max="2035" value="{{ $lap->tahun }}" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 font-normal dark:border-zinc-700 dark:bg-zinc-800">
                                                </label>
                                                <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                                    <span>Timbulan sampah</span>
                                                    <input type="number" step="0.1" min="0" name="timbulan_sampah_kg" value="{{ $lap->timbulan_sampah_kg }}" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 font-normal dark:border-zinc-700 dark:bg-zinc-800">
                                                </label>
                                                @foreach (['kualitas_air' => 'Kualitas air / IPAL', 'emisi_udara' => 'Emisi / udara', 'limbah_b3' => 'Limbah B3'] as $monitoringKey => $monitoringLabel)
                                                    <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                                        <span>{{ $monitoringLabel }}</span>
                                                        <input name="monitoring_data[{{ $monitoringKey }}]" value="{{ data_get($lap->monitoring_data, $monitoringKey) }}" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 font-normal dark:border-zinc-700 dark:bg-zinc-800">
                                                    </label>
                                                @endforeach
                                                <label class="space-y-1 font-semibold text-zinc-700 sm:col-span-2 lg:col-span-3 dark:text-zinc-300">
                                                    <span>Metode pengelolaan</span>
                                                    <textarea name="metode_pengelolaan" rows="2" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 font-normal dark:border-zinc-700 dark:bg-zinc-800">{{ $lap->metode_pengelolaan }}</textarea>
                                                </label>
                                                <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                                    <span>Ganti / tambah berkas</span>
                                                    <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" class="block w-full text-[11px] font-normal text-zinc-500">
                                                </label>
                                                <div class="flex flex-wrap items-center gap-2 sm:col-span-2 lg:col-span-4">
                                                    <button type="submit" class="civic-button-secondary">Simpan Perubahan</button>
                                                    <button type="submit" name="submit" value="1" class="civic-button-primary">Kirim ke DLH</button>
                                                </div>
                                            </form>
                                            @if ($lap->status === 'draft')
                                                <form method="POST" action="{{ route('dashboard.pemrakarsa.reports.destroy', $lap) }}" class="mt-2" onsubmit="return confirm('Hapus draft laporan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-xs font-bold text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/30">
                                                        <flux:icon.trash class="size-4" /> Hapus Draft
                                                    </button>
                                                </form>
                                            @endif
                                        </details>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-zinc-500">
                                    <p class="font-semibold text-sm">Tidak ada dokumen laporan ditemukan.</p>
                                    <p class="text-xs mt-1">Gunakan tombol "Unggah Laporan Baru" untuk menyampaikan berkas semesteran Anda ke DLH Kota Madiun.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($reports->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
