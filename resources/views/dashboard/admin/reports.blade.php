<x-layouts::app :title="__('Verifikasi Laporan Lingkungan - Admin DLH')">
    <div class="civic-page">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Verifikasi Laporan Lingkungan"
            eyebrow="Kepatuhan Lingkungan"
            title="Verifikasi Dokumen Lingkungan Semesteran"
            description="Evaluasi kepatuhan dokumen SPPL, UKL-UPL, dan AMDAL dari pelaku usaha dan pengelola fasilitas Kota Madiun."
            icon="document-check"
        >
            <x-slot:actions>
                <span class="civic-status-warning !px-3.5 !py-2 !text-xs">
                    {{ $reportCounts['menunggu_verifikasi'] }} Menunggu Verifikasi
                </span>
            </x-slot:actions>
        </x-civic.subpage-header>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Filter Bar -->
        <div data-ajax-zone="admin-reports" data-ajax-targets="#reportsStatusTabs,#reportsResults" class="space-y-6">
            <div class="civic-card flex flex-col justify-between gap-4 p-4 text-xs md:flex-row md:items-center">
                <div id="reportsStatusTabs" class="civic-filter-rail">
                    <a href="{{ route('dashboard.admin.reports', ['jenis_dokumen' => $currentJenis]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ ! $currentStatus ? 'civic-filter-chip-active' : '' }}">
                        Semua ({{ $reportCounts['all'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.reports', ['status' => 'menunggu_verifikasi', 'jenis_dokumen' => $currentJenis]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'menunggu_verifikasi' ? 'civic-filter-chip-active' : '' }}">
                        Menunggu ({{ $reportCounts['menunggu_verifikasi'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.reports', ['status' => 'perlu_cek_lapangan', 'jenis_dokumen' => $currentJenis]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'perlu_cek_lapangan' ? 'civic-filter-chip-active' : '' }}">
                        Cek Lapangan ({{ $reportCounts['perlu_cek_lapangan'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.reports', ['status' => 'disetujui', 'jenis_dokumen' => $currentJenis]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'disetujui' ? 'civic-filter-chip-active' : '' }}">
                        Disetujui ({{ $reportCounts['disetujui'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.reports', ['status' => 'perlu_revisi', 'jenis_dokumen' => $currentJenis]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'perlu_revisi' ? 'civic-filter-chip-active' : '' }}">
                        Perlu Revisi ({{ $reportCounts['perlu_revisi'] }})
                    </a>
                </div>

                <!-- Filter Jenis Dokumen -->
                <form id="reportsFilterForm" method="GET" action="{{ route('dashboard.admin.reports') }}" data-ajax-form data-ajax-targets="#reportsStatusTabs,#reportsResults" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $currentStatus ?? '' }}">
                    <div class="w-44">
                        <x-public.select-filter
                            name="jenis_dokumen"
                            :value="$currentJenis ?? ''"
                            :options="[
                                '' => 'Semua Dokumen',
                                'SPPL' => 'SPPL',
                                'UKL-UPL' => 'UKL-UPL',
                                'AMDAL' => 'AMDAL',
                            ]"
                            placeholder="Semua Dokumen"
                            heightClass="h-9 text-xs"
                            alignRight
                        />
                    </div>
                </form>
            </div>

            <!-- Daftar Dokumen Semester -->
            <div id="reportsResults" class="space-y-4">
                @forelse ($reports as $report)
                    @php
                        $reportStatusClass = $report->status === 'disetujui'
                            ? 'civic-status-success'
                            : ($report->status === 'perlu_revisi'
                                ? 'civic-status-danger'
                                : ($report->status === 'perlu_cek_lapangan' ? 'civic-status-info' : 'civic-status-warning'));
                    @endphp
                    <div class="civic-card space-y-4 p-5 text-xs">
                        <!-- Baris Atas Info Pemrakarsa -->
                        <div class="flex flex-col justify-between gap-2 border-b border-slate-100 pb-3 sm:flex-row sm:items-center dark:border-slate-800">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-lg bg-civic-100 px-2.5 py-1 font-mono text-xs font-bold text-civic-800 dark:bg-civic-950/60 dark:text-civic-300">
                                    {{ $report->jenis_dokumen }}
                                </span>
                                <span class="font-display text-sm font-extrabold text-slate-950 dark:text-white">
                                    {{ $report->pemrakarsa->pemrakarsaProfile->nama_instansi ?? $report->pemrakarsa->name }}
                                </span>
                                <span class="text-slate-400">•</span>
                                <span class="text-slate-500 dark:text-slate-400">NIB: {{ $report->pemrakarsa->pemrakarsaProfile->nib ?? '-' }}</span>
                                <span class="text-slate-400">•</span>
                                <span class="text-slate-500 dark:text-slate-400">{{ $report->created_at->translatedFormat('d M Y') }}</span>
                                @if ($report->nomor_laporan)
                                    <span class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $report->nomor_laporan }}</span>
                                @endif
                                @if ($report->nomor_bap)
                                    <span class="rounded-md bg-civic-100 px-2 py-1 font-mono text-xs font-bold text-civic-800 dark:bg-civic-950 dark:text-civic-200">BAP {{ $report->nomor_bap }}</span>
                                @endif
                            </div>
                            <span class="self-start sm:self-auto {{ $reportStatusClass }} uppercase tracking-wider">
                                {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                            </span>
                        </div>

                        <!-- Detail Laporan -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="space-y-3 md:col-span-2">
                                <h3 class="font-display text-sm font-extrabold text-slate-950 dark:text-white">{{ $report->judul_laporan }}</h3>
                                <div class="grid grid-cols-2 gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                    <div>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">Periode Laporan</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $report->periode_semester }} Tahun {{ $report->tahun }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">Timbulan Sampah Periode Ini</span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ number_format($report->timbulan_sampah_kg ?? 0, 1) }} Kg</span>
                                    </div>
                                </div>

                                @if ($report->metode_pengelolaan)
                                    <p class="text-slate-600 dark:text-slate-300">
                                        <strong class="text-slate-900 dark:text-slate-100">Metode Pengelolaan:</strong> {{ $report->metode_pengelolaan }}
                                    </p>
                                @endif

                                @if (filled($report->monitoring_data))
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                        @foreach (collect($report->monitoring_data)->filter()->take(3) as $monitoringLabel => $monitoringValue)
                                            <div class="rounded-xl border border-civic-100 bg-civic-50/70 p-2.5 dark:border-civic-900 dark:bg-civic-950/20">
                                                <span class="block text-xs font-bold uppercase tracking-wide text-civic-700 dark:text-civic-300">{{ str($monitoringLabel)->replace('_', ' ')->title() }}</span>
                                                <span class="mt-1 block font-semibold text-slate-800 dark:text-slate-200">{{ $monitoringValue }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($report->catatan_verifikasi)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/40">
                                        <span class="mb-0.5 block font-semibold text-slate-700 dark:text-slate-300">Catatan Evaluasi DLH:</span>
                                        <p class="italic text-slate-600 dark:text-slate-400">"{{ $report->catatan_verifikasi }}"</p>
                                    </div>
                                @endif

                                <!-- Status Penugasan Cek Fisik Lapangan (SRS Butir 2) -->
                                @if ($report->assignedPetugas)
                                    <div class="flex items-start gap-2.5 rounded-xl border border-sky-200 bg-sky-50/70 p-3 text-xs text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
                                        <flux:icon.user class="mt-0.5 size-5 shrink-0 text-sky-700 dark:text-sky-300" />
                                        <div class="flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-bold">Pengawas Lapangan Ditugaskan Cek Lokasi:</span>
                                                <span class="rounded-md bg-sky-200/80 px-2 py-0.5 font-extrabold text-sky-950 dark:bg-sky-900 dark:text-sky-100">{{ $report->assignedPetugas->name }}</span>
                                                @if ($report->assigned_at)
                                                    <span class="text-slate-500 dark:text-slate-400">• Ditugaskan: {{ $report->assigned_at->translatedFormat('d M Y') }}</span>
                                                @endif
                                            </div>
                                            @if ($report->instruksi_lapangan)
                                                <p class="mt-1.5 rounded-lg border border-sky-100 bg-white/80 p-2 italic text-slate-700 dark:border-sky-950 dark:bg-slate-900/60 dark:text-slate-300">
                                                    <strong>Instruksi Inspeksi:</strong> "{{ $report->instruksi_lapangan }}"
                                                </p>
                                            @endif
                                            @if ($report->catatan_inspeksi_petugas)
                                                <div class="mt-2.5 rounded-xl border border-emerald-200 bg-emerald-50/80 p-3 text-xs text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                                                    <div class="mb-1 flex items-center justify-between gap-2">
                                                        <span class="flex items-center gap-1.5 font-bold">
                                                            <flux:icon.magnifying-glass class="size-4" />
                                                            Temuan Hasil Cek Lapangan Petugas:
                                                        </span>
                                                        <span class="rounded-full px-2 py-0.5 text-xs font-bold uppercase tracking-wider {{ $report->hasil_inspeksi_lapangan === 'sesuai' ? 'bg-emerald-200 text-emerald-900 dark:bg-emerald-800 dark:text-emerald-100' : ($report->hasil_inspeksi_lapangan === 'tidak_sesuai' ? 'bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100' : 'bg-amber-200 text-amber-900 dark:bg-amber-800 dark:text-amber-100') }}">
                                                            {{ str_replace('_', ' ', $report->hasil_inspeksi_lapangan ?? 'Selesai Dicek') }}
                                                        </span>
                                                    </div>
                                                    <p class="rounded-lg border border-emerald-100 bg-white/80 p-2 italic text-slate-700 dark:border-emerald-900 dark:bg-slate-900/70 dark:text-slate-300">
                                                        "{{ $report->catatan_inspeksi_petugas }}"
                                                    </p>
                                                    @if ($report->inspeksi_at)
                                                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Waktu Inspeksi: {{ $report->inspeksi_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Unduh Berkas Dokumen -->
                            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-4 text-center md:col-span-1 dark:border-slate-700 dark:bg-slate-800/30">
                                <span class="civic-icon-tile mb-2"><flux:icon.document-text class="size-5" /></span>
                                <span class="mb-1.5 font-semibold text-slate-800 dark:text-slate-200">Berkas Dokumen</span>
                                @if ($report->file_path)
                                    <a href="{{ asset('storage/' . $report->file_path) }}" target="_blank" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                                        Unduh / Lihat Berkas
                                        <flux:icon.arrow-top-right-on-square class="size-3.5" />
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">Berkas belum diunggah.</span>
                                @endif
                            </div>
                        </div>

                        <!-- Formulir Aksi Verifikasi DLH & Penugasan Cek Fisik Lapangan -->
                        <div class="space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <!-- Form 1: Evaluasi Dokumen Langsung -->
                            <form method="POST" action="{{ route('dashboard.admin.reports.verify', $report->id) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <input type="text" name="catatan_verifikasi" placeholder="Tuliskan catatan evaluasi, dasar regulasi, atau revisi yang diperlukan..." value="{{ $report->catatan_verifikasi }}" class="min-h-10 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-900 focus:border-civic-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                    <div class="flex shrink-0 items-center gap-2">
                                        <button type="submit" name="status" value="disetujui" class="civic-button-primary !min-h-10 !py-2 !text-xs">
                                            ✓ Setujui Dokumen
                                        </button>
                                        <button type="submit" name="status" value="perlu_revisi" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-amber-700">
                                            Minta Revisi
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Form 2: Disposisi Penugasan Cek Fisik Lapangan -->
                            <form method="POST" action="{{ route('dashboard.admin.reports.verify', $report->id) }}" class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50/80 p-3 sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-800/50">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="perlu_cek_lapangan">
                                <span class="shrink-0 font-semibold text-slate-700 dark:text-slate-300">Inspeksi Lapangan:</span>
                                <select name="assigned_petugas_id" required class="min-h-10 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-900 focus:border-civic-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Tugaskan Pengawas Lapangan --</option>
                                    @foreach ($petugasList as $p)
                                        <option value="{{ $p->id }}" @selected($report->assigned_petugas_id === $p->id)>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="instruksi_lapangan" placeholder="Instruksi inspeksi lapangan (contoh: cek kepatuhan instalasi IPAL / TPS)..." value="{{ $report->instruksi_lapangan }}" class="min-h-10 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-civic-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                <button type="submit" class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl bg-civic-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-civic-800">
                                    {{ $report->assigned_petugas_id ? 'Ubah Penugasan' : 'Tugaskan Cek Lapangan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="civic-empty py-12">
                        Belum ada dokumen lingkungan yang masuk.
                    </div>
                @endforelse

                @if ($reports->hasPages())
                    <div class="civic-card p-4">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
