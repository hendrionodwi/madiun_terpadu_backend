<x-layouts::app :title="__('Verifikasi Laporan Lingkungan - Admin DLH')">
    <div class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Verifikasi Laporan Lingkungan</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Verifikasi Dokumen Lingkungan Semesteran</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Evaluasi kepatuhan dokumen SPPL, UKL-UPL, dan AMDAL dari pelaku usaha dan pengelola fasilitas Kota Madiun.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-lg bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 text-xs font-bold">
                    {{ $reportCounts['menunggu_verifikasi'] }} Menunggu Verifikasi
                </span>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <a href="{{ route('dashboard.admin.reports', ['jenis_dokumen' => $currentJenis]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ ! $currentStatus ? 'bg-purple-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Semua ({{ $reportCounts['all'] }})
                </a>
                <a href="{{ route('dashboard.admin.reports', ['status' => 'menunggu_verifikasi', 'jenis_dokumen' => $currentJenis]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'menunggu_verifikasi' ? 'bg-amber-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Menunggu ({{ $reportCounts['menunggu_verifikasi'] }})
                </a>
                <a href="{{ route('dashboard.admin.reports', ['status' => 'perlu_cek_lapangan', 'jenis_dokumen' => $currentJenis]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'perlu_cek_lapangan' ? 'bg-blue-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Cek Lapangan ({{ $reportCounts['perlu_cek_lapangan'] }})
                </a>
                <a href="{{ route('dashboard.admin.reports', ['status' => 'disetujui', 'jenis_dokumen' => $currentJenis]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'disetujui' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Disetujui ({{ $reportCounts['disetujui'] }})
                </a>
                <a href="{{ route('dashboard.admin.reports', ['status' => 'perlu_revisi', 'jenis_dokumen' => $currentJenis]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'perlu_revisi' ? 'bg-red-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Perlu Revisi ({{ $reportCounts['perlu_revisi'] }})
                </a>
            </div>

            <!-- Filter Jenis Dokumen -->
            <form method="GET" action="{{ route('dashboard.admin.reports') }}" class="flex items-center gap-2">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <select name="jenis_dokumen" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-medium">
                    <option value="">Semua Dokumen</option>
                    <option value="SPPL" {{ $currentJenis === 'SPPL' ? 'selected' : '' }}>SPPL</option>
                    <option value="UKL-UPL" {{ $currentJenis === 'UKL-UPL' ? 'selected' : '' }}>UKL-UPL</option>
                    <option value="AMDAL" {{ $currentJenis === 'AMDAL' ? 'selected' : '' }}>AMDAL</option>
                </select>
            </form>
        </div>

        <!-- Daftar Dokumen Semester -->
        <div class="space-y-4">
            @forelse ($reports as $report)
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4 text-xs">
                    <!-- Baris Atas Info Pemrakarsa -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md font-mono font-bold text-xs bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                                {{ $report->jenis_dokumen }}
                            </span>
                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $report->pemrakarsa->pemrakarsaProfile->nama_instansi ?? $report->pemrakarsa->name }}
                            </span>
                            <span class="text-zinc-400">•</span>
                            <span class="text-zinc-500">NIB: {{ $report->pemrakarsa->pemrakarsaProfile->nib ?? '-' }}</span>
                            <span class="text-zinc-400">•</span>
                            <span class="text-zinc-500">{{ $report->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <span class="self-start sm:self-auto px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $report->status === 'disetujui' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' :
                               ($report->status === 'perlu_revisi' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300') }}">
                            {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                        </span>
                    </div>

                    <!-- Detail Laporan -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2 space-y-2">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $report->judul_laporan }}</h3>
                            <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Periode Laporan</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $report->periode_semester }} Tahun {{ $report->tahun }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Timbulan Sampah Periode Ini</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ number_format($report->timbulan_sampah_kg ?? 0, 1) }} Kg</span>
                                </div>
                            </div>
                            @if ($report->metode_pengelolaan)
                                <p class="text-zinc-600 dark:text-zinc-300">
                                    <strong class="text-zinc-900 dark:text-zinc-100">Metode Pengelolaan:</strong> {{ $report->metode_pengelolaan }}
                                </p>
                            @endif
                            @if ($report->catatan_verifikasi)
                                <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300 block mb-0.5">Catatan Evaluasi DLH:</span>
                                    <p class="italic text-zinc-600 dark:text-zinc-400">"{{ $report->catatan_verifikasi }}"</p>
                                </div>
                            @endif

                            <!-- Status Penugasan Cek Fisik Lapangan (SRS Butir 2) -->
                            @if ($report->assignedPetugas)
                                <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/50 text-xs text-purple-900 dark:text-purple-200">
                                    <flux:icon.user class="size-5 shrink-0" />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold">Pengawas Lapangan Ditugaskan Cek Lokasi:</span>
                                            <span class="bg-purple-200 dark:bg-purple-900 text-purple-900 dark:text-purple-100 font-extrabold px-2 py-0.5 rounded-md">{{ $report->assignedPetugas->name }}</span>
                                            @if ($report->assigned_at)
                                                <span class="text-zinc-500">• Ditugaskan: {{ $report->assigned_at->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </div>
                                        @if ($report->instruksi_lapangan)
                                            <p class="mt-1 text-zinc-700 dark:text-zinc-300 italic bg-white/60 dark:bg-zinc-900/60 p-2 rounded-lg border border-purple-100 dark:border-purple-950">
                                                <strong>Instruksi Inspeksi:</strong> "{{ $report->instruksi_lapangan }}"
                                            </p>
                                        @endif
                                        @if ($report->catatan_inspeksi_petugas)
                                            <div class="mt-2.5 p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-900 dark:text-emerald-200">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-bold flex items-center gap-1.5">
                                                        <flux:icon.magnifying-glass class="size-4" />
                                                        Temuan Hasil Cek Lapangan Petugas:
                                                    </span>
                                                    <span class="font-bold uppercase tracking-wider text-[10px] px-2 py-0.5 rounded-full {{ $report->hasil_inspeksi_lapangan === 'sesuai' ? 'bg-emerald-200 text-emerald-900 dark:bg-emerald-800 dark:text-emerald-100' : ($report->hasil_inspeksi_lapangan === 'tidak_sesuai' ? 'bg-rose-200 text-rose-900 dark:bg-rose-800 dark:text-rose-100' : 'bg-amber-200 text-amber-900 dark:bg-amber-800 dark:text-amber-100') }}">
                                                        {{ str_replace('_', ' ', $report->hasil_inspeksi_lapangan ?? 'Selesai Dicek') }}
                                                    </span>
                                                </div>
                                                <p class="text-zinc-700 dark:text-zinc-300 italic bg-white/70 dark:bg-zinc-900/70 p-2 rounded-lg border border-emerald-100 dark:border-emerald-900">
                                                    "{{ $report->catatan_inspeksi_petugas }}"
                                                </p>
                                                @if ($report->inspeksi_at)
                                                    <span class="block mt-1 text-[10px] text-zinc-500">Waktu Inspeksi: {{ $report->inspeksi_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Unduh Berkas Dokumen -->
                        <div class="md:col-span-1 flex flex-col justify-center items-center p-4 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/20 text-center">
                            <flux:icon.document-text class="mb-2 size-8 text-zinc-500" />
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 mb-1">Berkas Dokumen</span>
                            <a href="{{ asset('storage/' . $report->file_path) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 font-semibold text-white transition hover:opacity-90 dark:bg-zinc-100 dark:text-zinc-900">
                                Unduh / Lihat Berkas
                                <flux:icon.arrow-top-right-on-square class="size-3.5" />
                            </a>
                        </div>
                    </div>

                    <!-- Formulir Aksi Verifikasi DLH & Penugasan Cek Fisik Lapangan (SRS Butir 2 & 4) -->
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
                        <!-- Form 1: Evaluasi Dokumen Langsung -->
                        <form method="POST" action="{{ route('dashboard.admin.reports.verify', $report->id) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <input type="text" name="catatan_verifikasi" placeholder="Tuliskan catatan evaluasi, dasar regulasi, atau revisi yang diperlukan..." value="{{ $report->catatan_verifikasi }}" class="flex-1 px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 text-xs">
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="submit" name="status" value="disetujui" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                                        ✓ Setujui Dokumen
                                    </button>
                                    <button type="submit" name="status" value="perlu_revisi" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition shadow-sm">
                                        Minta Revisi
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Form 2: Disposisi Penugasan Cek Fisik Lapangan (SRS Butir 2) -->
                        <form method="POST" action="{{ route('dashboard.admin.reports.verify', $report->id) }}" class="flex flex-col sm:flex-row sm:items-center gap-2 p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="perlu_cek_lapangan">
                            <span class="text-zinc-600 dark:text-zinc-300 font-semibold shrink-0">Inspeksi Lapangan:</span>
                            <select name="assigned_petugas_id" required class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 text-xs font-medium">
                                <option value="">-- Tugaskan Pengawas Lapangan --</option>
                                @foreach ($petugasList as $p)
                                    <option value="{{ $p->id }}" @selected($report->assigned_petugas_id === $p->id)>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="instruksi_lapangan" placeholder="Instruksi inspeksi lapangan (contoh: cek kepatuhan instalasi IPAL / TPS)..." value="{{ $report->instruksi_lapangan }}" class="flex-1 px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 text-xs">
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition shadow-sm shrink-0">
                                {{ $report->assigned_petugas_id ? 'Ubah Penugasan' : 'Tugaskan Cek Lapangan' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-12 text-center text-zinc-400 text-xs">
                    Belum ada dokumen lingkungan yang masuk.
                </div>
            @endforelse

            @if ($reports->hasPages())
                <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
