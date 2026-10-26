<x-layouts::app :title="__('Riwayat & Status Verifikasi Laporan Lingkungan')">
    <div class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Header Page -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-6 text-white shadow-lg">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center rounded-full bg-indigo-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-200 border border-indigo-500/30">
                        PPL-F-008 • Pemrakarsa Kegiatan
                    </span>
                    <span class="text-xs text-indigo-200">Kewajiban Pelaporan Dokumen Lingkungan DLH Kota Madiun</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Riwayat & Status Verifikasi Pelaporan
                </h1>
                <p class="text-sm text-indigo-100/90 mt-1">
                    Pantau dokumen pemantauan lingkungan semesteran Anda yang sedang dievaluasi oleh DLH Kota Madiun.
                </p>
            </div>
            <div>
                <a
                    href="{{ route('dashboard.pemrakarsa.reports.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-indigo-900 hover:bg-indigo-50 shadow-sm transition"
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
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                @php
                    $filters = [
                        'all' => ['label' => 'Semua Dokumen', 'count' => $reportCounts['all']],
                        'menunggu_verifikasi' => ['label' => 'Menunggu Review', 'count' => $reportCounts['menunggu_verifikasi']],
                        'disetujui' => ['label' => 'Disetujui DLH', 'count' => $reportCounts['disetujui']],
                        'perlu_revisi' => ['label' => 'Perlu Revisi', 'count' => $reportCounts['perlu_revisi']],
                    ];
                @endphp

                @foreach ($filters as $key => $f)
                    <a
                        href="{{ route('dashboard.pemrakarsa.reports', array_merge(request()->query(), ['status' => $key])) }}"
                        class="px-3.5 py-2 rounded-xl font-semibold transition shrink-0 {{ $currentStatus === $key ? 'bg-indigo-700 text-white shadow-sm' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 border border-zinc-200 dark:border-zinc-700' }}"
                    >
                        {{ $f['label'] }} ({{ $f['count'] }})
                    </a>
                @endforeach
            </div>

            <!-- Filter Jenis Dokumen & Search Form -->
            <form method="GET" action="{{ route('dashboard.pemrakarsa.reports') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <select
                    name="jenis_dokumen"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs px-3 py-2 text-zinc-700 dark:text-zinc-200"
                >
                    <option value="all" {{ $currentJenis === 'all' ? 'selected' : '' }}>Semua Izin</option>
                    <option value="SPPL" {{ $currentJenis === 'SPPL' ? 'selected' : '' }}>SPPL</option>
                    <option value="UKL-UPL" {{ $currentJenis === 'UKL-UPL' ? 'selected' : '' }}>UKL-UPL</option>
                    <option value="AMDAL" {{ $currentJenis === 'AMDAL' ? 'selected' : '' }}>AMDAL</option>
                </select>

                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari judul laporan..."
                        class="rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs px-3 py-2 text-zinc-700 dark:text-zinc-200 w-48 sm:w-56"
                    />
                </div>

                <button type="submit" class="px-3 py-2 rounded-xl bg-zinc-800 text-white text-xs font-semibold hover:bg-zinc-700">
                    Cari
                </button>
            </form>
        </div>

        <!-- Tabel Laporan -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[11px] font-bold border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="p-3.5">Judul Dokumen</th>
                            <th class="p-3.5">Periode Lapor</th>
                            <th class="p-3.5">Timbulan Sampah</th>
                            <th class="p-3.5">Status Validasi DLH</th>
                            <th class="p-3.5">Tanggal Unggah</th>
                            <th class="p-3.5 text-center">Berkas</th>
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
                                </td>
                                <td class="p-3.5 text-zinc-700 dark:text-zinc-300 font-medium">
                                    {{ $lap->periode_semester }} <br>
                                    <span class="text-xs text-zinc-500">Tahun {{ $lap->tahun }}</span>
                                </td>
                                <td class="p-3.5 font-bold text-zinc-800 dark:text-zinc-200">
                                    {{ $lap->timbulan_sampah_kg ? number_format($lap->timbulan_sampah_kg, 1, ',', '.') . ' Kg' : '-' }}
                                </td>
                                <td class="p-3.5">
                                    @if ($lap->status === 'menunggu_verifikasi')
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
                                <td class="p-3.5 text-zinc-500">
                                    {{ $lap->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <a
                                        href="{{ asset('storage/' . $lap->file_path) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 font-semibold transition"
                                    >
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Unduh
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-zinc-500">
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
