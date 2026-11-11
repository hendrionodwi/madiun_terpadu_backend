<x-layouts::app :title="__('Manajemen Entitas & Fasilitas TPS3R - Admin DLH')">
    <div class="civic-page">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Entitas & TPS3R"
            eyebrow="Validasi & Fasilitas"
            title="Validasi & Manajemen Entitas"
            description="Pemantauan fasilitas TPS3R resmi serta legalitas badan usaha/pemrakarsa di Kota Madiun."
            icon="building-office-2"
        />

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

        <section class="civic-card overflow-hidden">
            <div class="civic-section-header">
                <div>
                    <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Antrean Validasi Legalitas</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pengajuan terbaru dari pemrakarsa dan pengelola TPS3R.</p>
                </div>
            </div>
            <div class="civic-table-wrap rounded-none border-0">
                <table class="civic-table">
                    <thead>
                        <tr>
                            <th>Entitas</th>
                            <th>Dokumen</th>
                            <th>Status</th>
                            <th>Peninjauan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entityVerifications as $verification)
                            @php
                                $verClass = $verification->status === 'approved' ? 'civic-status-success' : ($verification->status === 'rejected' ? 'civic-status-danger' : 'civic-status-warning');
                            @endphp
                            <tr>
                                <td>
                                    <p class="font-bold text-slate-950 dark:text-white">{{ $verification->user->name }}</p>
                                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">{{ $verification->entity_type }}</p>
                                </td>
                                <td>
                                    <p class="font-mono text-xs text-slate-700 dark:text-slate-300">{{ $verification->document_number ?? '-' }}</p>
                                    @if ($verification->document_path)
                                        <a href="{{ route('dashboard.admin.entities.download', $verification) }}" class="civic-link mt-1">Unduh berkas</a>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $verClass }} uppercase">{{ $verification->status }}</span>
                                    @if ($verification->admin_note)
                                        <p class="mt-2 max-w-xs text-xs text-slate-500 dark:text-slate-400">{{ $verification->admin_note }}</p>
                                    @endif
                                </td>
                                <td>
                                    @if ($verification->status === 'submitted')
                                        <form method="POST" action="{{ route('dashboard.admin.entities.update', $verification) }}" class="flex min-w-72 gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input name="admin_note" placeholder="Catatan validasi" class="min-w-36 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-civic-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                            <button name="status" value="approved" class="rounded-xl bg-civic-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-civic-800">Setujui</button>
                                            <button name="status" value="rejected" class="rounded-xl bg-red-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-red-700">Tolak</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Ditinjau {{ $verification->reviewed_at?->format('d M Y') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada pengajuan legalitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Tab Navigasi -->
        <div data-ajax-zone="admin-entities" class="space-y-6">
            <div class="civic-filter-rail">
                <a href="{{ route('dashboard.admin.entities', ['tab' => 'pemrakarsa']) }}" data-ajax-link class="civic-filter-chip inline-flex items-center gap-2 !text-xs {{ $activeTab === 'pemrakarsa' ? 'civic-filter-chip-active' : '' }}">
                    <flux:icon.building-office-2 class="size-4" />
                    Pemrakarsa Kegiatan / Usaha ({{ $pemrakarsaList->total() }})
                </a>
                <a href="{{ route('dashboard.admin.entities', ['tab' => 'tps3r']) }}" data-ajax-link class="civic-filter-chip inline-flex items-center gap-2 !text-xs {{ $activeTab === 'tps3r' ? 'civic-filter-chip-active' : '' }}">
                    <flux:icon.arrow-path-rounded-square class="size-4" />
                    Fasilitas TPS3R Kota ({{ $tps3rList->total() }})
                </a>
            </div>

            @if ($activeTab === 'tps3r')
                <!-- TAB 1: FASILITAS TPS3R -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($tps3rList as $tps3r)
                        <div class="civic-card-interactive space-y-3 p-5 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <span class="rounded-lg bg-civic-100 px-2.5 py-1 font-mono text-xs font-bold text-civic-800 dark:bg-civic-950/60 dark:text-civic-300">
                                    {{ $tps3r->kode_tps3r }}
                                </span>
                                <span class="civic-status-info">
                                    Beroperasi
                                </span>
                            </div>
                            <div>
                                <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $tps3r->nama_tps3r }}</h3>
                                <p class="mt-0.5 text-slate-500 dark:text-slate-400">{{ $tps3r->alamat }}, Kel. {{ $tps3r->kelurahan }}, Kec. {{ $tps3r->kecamatan }}</p>
                            </div>
                            <div class="space-y-1.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <div class="flex justify-between gap-2">
                                    <span class="text-slate-500 dark:text-slate-400">Pengelola Resmi:</span>
                                    <span class="font-bold text-slate-950 dark:text-white">{{ $tps3r->user->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <span class="text-slate-500 dark:text-slate-400">Kapasitas Harian:</span>
                                    <span class="font-bold text-civic-700 dark:text-civic-300">{{ number_format($tps3r->kapasitas_harian_kg) }} Kg/Hari</span>
                                </div>
                                @if ($tps3r->latitude && $tps3r->longitude)
                                    <div class="flex justify-between gap-2 text-xs text-slate-400">
                                        <span>Koordinat GPS:</span>
                                        <span class="font-mono">{{ $tps3r->latitude }}, {{ $tps3r->longitude }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-xs dark:border-slate-700/60">
                                    <span class="text-slate-500 dark:text-slate-400">Midtrans:</span>
                                    @if ($tps3r->isMidtransConfigured())
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-civic-700 dark:text-civic-300">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif ({{ $tps3r->midtrans_is_production ? 'Prod' : 'Sandbox' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-slate-400">
                                            <span class="size-1.5 rounded-full bg-slate-300"></span>
                                            Belum Konfigurasi
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-1">
                                <a href="{{ route('dashboard.tps3r.payment-settings', ['tps3r_user_id' => $tps3r->user_id]) }}" class="civic-button-secondary w-full justify-center !py-2 !text-xs">
                                    <flux:icon.credit-card class="size-3.5" />
                                    Lihat Setup Midtrans TPS3R
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="civic-empty col-span-3">Belum ada fasilitas TPS3R terdaftar.</div>
                    @endforelse
                </div>

                @if ($tps3rList->hasPages())
                    <div class="civic-card p-4">
                        {{ $tps3rList->links() }}
                    </div>
                @endif
            @else
                <!-- TAB 2: PEMRAKARSA KEGIATAN -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($pemrakarsaList as $pem)
                        <div class="civic-card-interactive space-y-3 p-5 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <span class="rounded-lg bg-civic-100 px-2.5 py-1 font-mono text-xs font-bold text-civic-800 dark:bg-civic-950/60 dark:text-civic-300">
                                    NIB: {{ $pem->nib }}
                                </span>
                                <span class="civic-status-success">
                                    Terverifikasi
                                </span>
                            </div>
                            <div>
                                <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $pem->nama_instansi }}</h3>
                                <span class="mt-0.5 inline-block text-slate-500 dark:text-slate-400">{{ $pem->jenis_instansi ?? 'Sektor Usaha' }}</span>
                            </div>
                            <div class="space-y-1.5 rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <div class="flex justify-between gap-2">
                                    <span class="text-slate-500 dark:text-slate-400">Penanggung Jawab:</span>
                                    <span class="font-bold text-slate-950 dark:text-white">{{ $pem->penanggung_jawab }}</span>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <span class="text-slate-500 dark:text-slate-400">Akun Pengguna:</span>
                                    <span class="text-slate-800 dark:text-slate-200">{{ $pem->user->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <span class="text-slate-500 dark:text-slate-400">Alamat:</span>
                                    <span class="max-w-[180px] truncate text-slate-800 dark:text-slate-200">{{ $pem->alamat_instansi }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="civic-empty col-span-3">Belum ada pemrakarsa usaha terdaftar.</div>
                    @endforelse
                </div>

                @if ($pemrakarsaList->hasPages())
                    <div class="civic-card p-4">
                        {{ $pemrakarsaList->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts::app>
