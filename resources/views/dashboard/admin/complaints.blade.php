<x-layouts::app :title="__('Kelola Pengaduan Masyarakat - Admin DLH')">
    <div class="civic-page">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Pengaduan Masyarakat"
            eyebrow="Pengawasan Lingkungan"
            title="Kelola Pengaduan Masyarakat (PPL-F-001)"
            description="Pantau laporan tumpukan sampah liar, tinjau hasil verifikasi petugas lapangan, dan finalisasi tindak lanjut."
            icon="megaphone"
        >
            <x-slot:actions>
                <span class="civic-status-warning !px-3.5 !py-2 !text-xs">
                    {{ $statusCounts['menunggu_verifikasi'] + $statusCounts['dalam_proses'] }} Menunggu / Diproses
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
        <div data-ajax-zone="admin-complaints" data-ajax-targets="#complaintsStatusTabs,#complaintsResults" class="space-y-6">
            @php
                $complaintCategoryOptions = ['' => 'Semua Kategori Aduan'];
                if (isset($complaintCategories) && $complaintCategories->isNotEmpty()) {
                    foreach ($complaintCategories as $cat) {
                        $complaintCategoryOptions[$cat->code] = $cat->name;
                    }
                } else {
                    $complaintCategoryOptions += [
                        'Sampah_Liar' => 'Tumpukan Sampah Liar',
                        'sampah_liar' => 'Sampah Liar',
                        'tps_meluap' => 'TPS / Kontainer Meluap',
                        'saluran_tersumbat' => 'Saluran Tersumbat',
                        'pembakaran_sampah' => 'Pembakaran Sampah',
                        'pencemaran_lingkungan' => 'Pencemaran Lingkungan',
                    ];
                }
                $complaintDistrictOptions = collect(['' => 'Semua Kecamatan'])
                    ->merge(collect($districts ?? ['Kartoharjo', 'Manguharjo', 'Taman'])->mapWithKeys(fn ($d) => [$d => 'Kec. ' . $d]))
                    ->all();
            @endphp

            <div class="civic-card flex flex-col justify-between gap-4 p-4 text-xs md:flex-row md:items-center">
                <div id="complaintsStatusTabs" class="civic-filter-rail">
                    <a href="{{ route('dashboard.admin.complaints', ['kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ ! $currentStatus ? 'civic-filter-chip-active' : '' }}">
                        Semua Status ({{ $statusCounts['all'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.complaints', ['status' => 'menunggu_verifikasi', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'menunggu_verifikasi' ? 'civic-filter-chip-active' : '' }}">
                        Menunggu ({{ $statusCounts['menunggu_verifikasi'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.complaints', ['status' => 'dalam_proses', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'dalam_proses' ? 'civic-filter-chip-active' : '' }}">
                        Dalam Proses ({{ $statusCounts['dalam_proses'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.complaints', ['status' => 'selesai', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'selesai' ? 'civic-filter-chip-active' : '' }}">
                        Selesai ({{ $statusCounts['selesai'] }})
                    </a>
                    <a href="{{ route('dashboard.admin.complaints', ['status' => 'ditolak', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentStatus === 'ditolak' ? 'civic-filter-chip-active' : '' }}">
                        Ditolak ({{ $statusCounts['ditolak'] }})
                    </a>
                </div>

                <!-- Filter Kecamatan & Kategori -->
                <form id="complaintsFilterForm" method="GET" action="{{ route('dashboard.admin.complaints') }}" data-ajax-form data-ajax-targets="#complaintsStatusTabs,#complaintsResults" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="status" value="{{ $currentStatus ?? '' }}">
                    <div class="w-52">
                        <x-public.select-filter
                            name="kategori"
                            :value="$currentCategory ?? ''"
                            :options="$complaintCategoryOptions"
                            placeholder="Semua Kategori Aduan"
                            heightClass="h-9 text-xs"
                            alignRight
                        />
                    </div>
                    <div class="w-44">
                        <x-public.select-filter
                            name="kecamatan"
                            :value="$currentDistrict ?? ''"
                            :options="$complaintDistrictOptions"
                            placeholder="Semua Kecamatan"
                            heightClass="h-9 text-xs"
                            alignRight
                        />
                    </div>
                </form>
            </div>

            <!-- Daftar Kartu Tiket Pengaduan -->
            <div id="complaintsResults" class="space-y-4">
                @forelse ($complaints as $aduan)
                    @php
                        $aduanStatusClass = $aduan->status === 'selesai'
                            ? 'civic-status-success'
                            : ($aduan->status === 'ditolak'
                                ? 'civic-status-danger'
                                : (in_array($aduan->status, ['ditindaklanjuti', 'diverifikasi_lapangan', 'ditugaskan', 'dalam_proses'], true)
                                    ? 'civic-status-info'
                                    : 'civic-status-warning'));
                    @endphp
                    <div class="civic-card space-y-4 p-5">
                        <!-- Header Aduan -->
                        <div class="flex flex-col justify-between gap-2 border-b border-slate-100 pb-3 sm:flex-row sm:items-center dark:border-slate-800">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-lg bg-civic-100 px-2.5 py-1 font-mono text-xs font-extrabold text-civic-800 dark:bg-civic-950/60 dark:text-civic-300">
                                    #{{ $aduan->nomor_tiket }}
                                </span>
                                @if ($aduan->is_anonim)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-800 px-2 py-0.5 text-xs font-bold text-slate-100 dark:bg-slate-700" title="Pelapor memilih merahasiakan identitas dari publik dan petugas">
                                        <flux:icon.lock-closed class="size-3" />
                                        Anonim
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-civic-100 px-2.5 py-0.5 text-xs font-semibold text-civic-800 dark:bg-civic-950/80 dark:text-civic-300">
                                    <flux:icon.folder-open class="size-3.5" />
                                    {{ (isset($complaintCategories) ? $complaintCategories->firstWhere('code', $aduan->kategori_masalah)?->name : null) ?? ucwords(str_replace('_', ' ', $aduan->kategori_masalah ?? 'sampah_liar')) }}
                                </span>
                                <span class="text-xs text-slate-400">•</span>
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Pelapor: {{ $aduan->pelapor->name ?? 'Warga' }}
                                    @if (! $aduan->is_anonim && $aduan->pelapor?->phone_number)
                                        ({{ $aduan->pelapor->phone_number }})
                                    @endif
                                </span>
                                <span class="text-xs text-slate-400">•</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $aduan->created_at->translatedFormat('d M Y, H:i') }} ({{ $aduan->created_at->diffForHumans() }})</span>
                            </div>
                            <span class="self-start sm:self-auto {{ $aduanStatusClass }} uppercase tracking-wider">
                                {{ $aduan->status_label ?? ucfirst(str_replace('_', ' ', $aduan->status)) }}
                            </span>
                        </div>

                        <!-- Detail Isi Aduan -->
                        <div class="grid grid-cols-1 gap-4 text-xs md:grid-cols-3">
                            <div class="space-y-2 md:col-span-2">
                                <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $aduan->judul }}</h3>
                                <p class="leading-relaxed text-slate-600 dark:text-slate-300">{{ $aduan->deskripsi }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-3 text-slate-500 dark:text-slate-400">
                                    <span class="inline-flex items-center gap-1.5"><flux:icon.map-pin class="size-3.5" /> {{ $aduan->lokasi }}</span>
                                    @if ($aduan->kecamatan)
                                        <span>• Kec. {{ $aduan->kecamatan }}</span>
                                    @endif
                                    @if ($aduan->latitude && $aduan->longitude)
                                        <span>• GPS: {{ $aduan->latitude }}, {{ $aduan->longitude }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Foto Bukti Awal -->
                            <div class="md:col-span-1">
                                @if ($aduan->foto)
                                    <div class="max-h-36 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                                        <img src="{{ $aduan->foto_url ?? asset('storage/' . $aduan->foto) }}" alt="Foto Aduan" class="h-36 w-full object-cover transition hover:scale-105">
                                    </div>
                                @else
                                    <div class="flex flex-col items-center gap-1.5 rounded-xl border border-dashed border-slate-300 p-4 text-center text-slate-400 dark:border-slate-700">
                                        <flux:icon.photo class="size-5" />
                                        <span>Tidak ada lampiran foto</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/60">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Prioritas</p>
                                <p class="mt-1 font-bold capitalize text-slate-950 dark:text-white">{{ $aduan->prioritas ?: 'Sedang' }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/60">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Rute</p>
                                <p class="mt-1 font-mono font-bold text-slate-950 dark:text-white">{{ $aduan->route_code ?: 'Belum ditentukan' }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/60">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Target selesai</p>
                                <p class="mt-1 font-bold text-slate-950 dark:text-white">{{ $aduan->target_selesai_at?->translatedFormat('d M Y, H:i') ?? 'Belum ditentukan' }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/60">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Aktivitas status</p>
                                <p class="mt-1 font-bold text-slate-950 dark:text-white">{{ $aduan->statusLogs->count() }} catatan</p>
                            </div>
                        </div>

                        @if ($aduan->statusLogs->isNotEmpty())
                            <details class="rounded-xl border border-slate-200 bg-slate-50/70 dark:border-slate-700 dark:bg-slate-800/40">
                                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between px-3 text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Riwayat status
                                    <flux:icon.chevron-down class="size-4" />
                                </summary>
                                <ol class="space-y-3 border-t border-slate-200 p-3 dark:border-slate-700">
                                    @foreach ($aduan->statusLogs->take(5) as $statusLog)
                                        <li class="flex gap-3 text-xs">
                                            <span class="mt-1 size-2 shrink-0 rounded-full bg-civic-600"></span>
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-950 dark:text-white">{{ $statusLog->judul_notifikasi }}</p>
                                                <p class="mt-0.5 text-slate-600 dark:text-slate-400">{{ $statusLog->pesan }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $statusLog->created_at?->translatedFormat('d M Y, H:i') }} WIB</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </details>
                        @endif

                        <!-- Status Penugasan Petugas Lapangan -->
                        @if ($aduan->assignedPetugas)
                            <div class="flex items-start gap-2.5 rounded-xl border border-sky-200 bg-sky-50/70 p-3 text-xs text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
                                <flux:icon.user class="mt-0.5 size-5 shrink-0" />
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold">Petugas Ditugaskan ke Lokasi:</span>
                                        <span class="rounded-md bg-sky-200/80 px-2 py-0.5 font-extrabold text-sky-950 dark:bg-sky-900 dark:text-sky-100">{{ $aduan->assignedPetugas->name }}</span>
                                        @if ($aduan->assigned_at)
                                            <span class="text-slate-500 dark:text-slate-400">• Disposisi: {{ $aduan->assigned_at->translatedFormat('d M Y, H:i') }}</span>
                                        @endif
                                    </div>
                                    @if ($aduan->instruksi_admin)
                                        <p class="mt-1.5 rounded-lg border border-sky-100 bg-white/80 p-2 italic text-slate-700 dark:border-sky-950 dark:bg-slate-900/60 dark:text-slate-300">
                                            <strong>Instruksi DLH:</strong> "{{ $aduan->instruksi_admin }}"
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Hasil Investigasi Petugas Lapangan -->
                        @if ($aduan->verifications->isNotEmpty())
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3.5 text-xs dark:border-emerald-900/40 dark:bg-emerald-950/20">
                                <h4 class="mb-2 flex items-center gap-1.5 font-bold text-emerald-900 dark:text-emerald-300">
                                    <flux:icon.magnifying-glass class="size-4" />
                                    Hasil Verifikasi & Tindak Lanjut Lapangan (PPL-F-004):
                                </h4>
                                @foreach ($aduan->verifications as $verif)
                                    <div class="space-y-1.5 text-slate-700 dark:text-slate-300">
                                        <p class="font-semibold">Petugas: {{ $verif->petugas->name ?? 'Petugas DLH' }} • {{ \Carbon\Carbon::parse($verif->tanggal_verifikasi)->translatedFormat('d M Y') }}</p>
                                        <p class="italic text-slate-600 dark:text-slate-400">Catatan Temuan: "{{ $verif->catatan_petugas ?? 'Sampah telah ditinjau.' }}"</p>
                                        @if ($verif->rekomendasi_tindak_lanjut)
                                            <div class="rounded-lg border border-emerald-300 bg-emerald-100/70 p-2 font-medium text-emerald-900 dark:border-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                <strong>Rekomendasi Tindak Lanjut:</strong> {{ $verif->rekomendasi_tindak_lanjut }}
                                            </div>
                                        @endif
                                        @if ($verif->foto_bukti)
                                            <div class="mt-2">
                                                <span class="font-semibold text-slate-500">Bukti Foto Petugas:</span>
                                                <img src="{{ asset('storage/' . $verif->foto_bukti) }}" alt="Bukti Verifikasi" class="mt-1 h-24 rounded-lg border border-emerald-200 object-cover">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Formulir Tindak Lanjut -->
                        <div class="border-t border-slate-100 pt-4 text-xs dark:border-slate-800">
                            @if ($aduan->status === 'selesai')
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-800/60 dark:bg-emerald-950/30">
                                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                        <div class="flex items-center gap-3">
                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-civic-700 text-base font-bold text-white shadow-sm" aria-hidden="true">✓</span>
                                            <div>
                                                <h4 class="font-bold text-emerald-950 dark:text-emerald-100">Tiket Aduan Telah Selesai Ditangani</h4>
                                                <p class="mt-0.5 text-xs text-emerald-800 dark:text-emerald-300">
                                                    Aduan telah tuntas dibersihkan dan status di aplikasi warga sudah otomatis selesai.
                                                    @if ($aduan->instruksi_admin)
                                                        <span class="mt-0.5 block font-medium italic text-slate-600 dark:text-slate-400">Catatan akhir: "{{ $aduan->instruksi_admin }}"</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="document.getElementById('edit-forms-{{ $aduan->id }}').classList.toggle('hidden')" class="civic-button-secondary !min-h-9 shrink-0 !px-3 !py-1.5 !text-xs">
                                            <flux:icon.pencil-square class="size-4" />
                                            Perbarui Catatan / Buka Kembali
                                        </button>
                                    </div>

                                    <div id="edit-forms-{{ $aduan->id }}" class="mt-4 hidden border-t border-emerald-200/60 pt-4 dark:border-emerald-800/60">
                                        <x-admin.complaint-action-forms :aduan="$aduan" :petugasList="$petugasList" :activeRoutes="$activeRoutes" />
                                    </div>
                                </div>
                            @elseif ($aduan->status === 'ditolak')
                                <div class="rounded-2xl border border-red-200 bg-red-50/80 p-4 dark:border-red-800/60 dark:bg-red-950/30">
                                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                        <div class="flex items-center gap-3">
                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-red-600 text-base font-bold text-white shadow-sm" aria-hidden="true">✕</span>
                                            <div>
                                                <h4 class="font-bold text-red-950 dark:text-red-100">Tiket Aduan Ditolak</h4>
                                                <p class="mt-0.5 text-xs text-red-700 dark:text-red-300">
                                                    Aduan dinyatakan tidak valid atau fiktif.
                                                    @if ($aduan->instruksi_admin)
                                                        <span class="mt-0.5 block font-medium italic text-slate-600 dark:text-slate-400">Alasan penolakan: "{{ $aduan->instruksi_admin }}"</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="document.getElementById('edit-forms-{{ $aduan->id }}').classList.toggle('hidden')" class="inline-flex min-h-9 shrink-0 items-center justify-center gap-1.5 rounded-xl border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-800 shadow-xs transition hover:bg-red-50 dark:border-red-700 dark:bg-slate-900 dark:text-red-200 dark:hover:bg-slate-800">
                                            <flux:icon.pencil-square class="size-4" />
                                            Kelola Ulang Tiket
                                        </button>
                                    </div>

                                    <div id="edit-forms-{{ $aduan->id }}" class="mt-4 hidden border-t border-red-200/60 pt-4 dark:border-red-800/60">
                                        <x-admin.complaint-action-forms :aduan="$aduan" :petugasList="$petugasList" :activeRoutes="$activeRoutes" />
                                    </div>
                                </div>
                            @else
                                <x-admin.complaint-action-forms :aduan="$aduan" :petugasList="$petugasList" :activeRoutes="$activeRoutes" />
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="civic-empty py-12">
                        Tidak ada pengaduan masyarakat yang sesuai filter.
                    </div>
                @endforelse

                @if ($complaints->hasPages())
                    <div class="civic-card p-4">
                        {{ $complaints->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
