<x-layouts::app :title="__('Manajemen Entitas & Fasilitas TPS3R - Admin DLH')">
    <div class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Entitas & TPS3R</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Validasi & Manajemen Entitas</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Pemantauan fasilitas TPS3R resmi serta legalitas badan usaha/pemrakarsa di Kota Madiun.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300"><ul class="list-disc space-y-1 ps-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700"><h2 class="font-bold text-zinc-950 dark:text-white">Antrean Validasi Legalitas</h2><p class="text-xs text-zinc-500">Pengajuan terbaru dari pemrakarsa dan pengelola TPS3R.</p></div>
            <div class="overflow-x-auto"><table class="w-full text-left text-xs"><thead class="bg-zinc-50 uppercase text-zinc-500 dark:bg-zinc-800"><tr><th class="px-4 py-3">Entitas</th><th class="px-4 py-3">Dokumen</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Peninjauan</th></tr></thead><tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">@forelse($entityVerifications as $verification)<tr><td class="px-4 py-4"><p class="font-bold text-zinc-950 dark:text-white">{{ $verification->user->name }}</p><p class="uppercase text-zinc-500">{{ $verification->entity_type }}</p></td><td class="px-4 py-4"><p class="font-mono">{{ $verification->document_number ?? '-' }}</p>@if($verification->document_path)<a href="{{ route('dashboard.admin.entities.download', $verification) }}" class="mt-1 inline-flex font-bold text-blue-700 hover:underline">Unduh berkas</a>@endif</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 font-bold uppercase {{ $verification->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($verification->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $verification->status }}</span>@if($verification->admin_note)<p class="mt-2 max-w-xs text-zinc-500">{{ $verification->admin_note }}</p>@endif</td><td class="px-4 py-4">@if($verification->status === 'submitted')<form method="POST" action="{{ route('dashboard.admin.entities.update', $verification) }}" class="flex min-w-72 gap-2">@csrf @method('PATCH')<input name="admin_note" placeholder="Catatan validasi" class="min-w-36 rounded-lg border-zinc-300 text-xs dark:border-zinc-700 dark:bg-zinc-800"><button name="status" value="approved" class="rounded-lg bg-emerald-700 px-3 py-2 font-bold text-white">Setujui</button><button name="status" value="rejected" class="rounded-lg bg-red-700 px-3 py-2 font-bold text-white">Tolak</button></form>@else<span class="text-zinc-400">Ditinjau {{ $verification->reviewed_at?->format('d M Y') }}</span>@endif</td></tr>@empty<tr><td colspan="4" class="px-4 py-8 text-center text-zinc-500">Belum ada pengajuan legalitas.</td></tr>@endforelse</tbody></table></div>
        </section>

        <!-- Tab Navigasi -->
        <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-700 pb-2 text-xs font-bold">
            <a href="{{ route('dashboard.admin.entities', ['tab' => 'pemrakarsa']) }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 transition {{ $activeTab === 'pemrakarsa' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200' }}">
                <flux:icon.building-office-2 class="size-4" />
                Pemrakarsa Kegiatan / Usaha ({{ $pemrakarsaList->total() }})
            </a>
            <a href="{{ route('dashboard.admin.entities', ['tab' => 'tps3r']) }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 transition {{ $activeTab === 'tps3r' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200' }}">
                <flux:icon.arrow-path-rounded-square class="size-4" />
                Fasilitas TPS3R Kota ({{ $tps3rList->total() }})
            </a>
        </div>

        @if ($activeTab === 'tps3r')
            <!-- TAB 1: FASILITAS TPS3R -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($tps3rList as $tps3r)
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded">
                                {{ $tps3r->kode_tps3r }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                Beroperasi
                            </span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $tps3r->nama_tps3r }}</h3>
                            <p class="text-zinc-500 mt-0.5">{{ $tps3r->alamat }}, Kel. {{ $tps3r->kelurahan }}, Kec. {{ $tps3r->kecamatan }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Pengelola Resmi:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $tps3r->user->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Kapasitas Harian:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($tps3r->kapasitas_harian_kg) }} Kg/Hari</span>
                            </div>
                            @if ($tps3r->latitude && $tps3r->longitude)
                                <div class="flex justify-between text-[11px] text-zinc-400">
                                    <span>Koordinat GPS:</span>
                                    <span>{{ $tps3r->latitude }}, {{ $tps3r->longitude }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between pt-2 border-t border-zinc-200 dark:border-zinc-700/60 text-[11px]">
                                <span class="text-zinc-500">Midtrans:</span>
                                @if ($tps3r->isMidtransConfigured())
                                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif ({{ $tps3r->midtrans_is_production ? 'Prod' : 'Sandbox' }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-zinc-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>
                                        Belum Konfigurasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="pt-1">
                            <a href="{{ route('dashboard.tps3r.payment-settings', ['tps3r_user_id' => $tps3r->user_id]) }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-zinc-100 px-3 py-1.5 text-[11px] font-semibold text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon.credit-card class="size-3.5" />
                                Lihat Setup Midtrans TPS3R
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-12 text-center text-zinc-400 text-xs">Belum ada fasilitas TPS3R terdaftar.</div>
                @endforelse
            </div>
            @if ($tps3rList->hasPages())
                <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    {{ $tps3rList->links() }}
                </div>
            @endif
        @else
            <!-- TAB 2: PEMRAKARSA KEGIATAN -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($pemrakarsaList as $pem)
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-md font-mono text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400">
                                NIB: {{ $pem->nib }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                Terverifikasi
                            </span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $pem->nama_instansi }}</h3>
                            <span class="inline-block mt-0.5 text-zinc-500">{{ $pem->jenis_instansi ?? 'Sektor Usaha' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Penanggung Jawab:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $pem->penanggung_jawab }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Akun Pengguna:</span>
                                <span class="text-zinc-800 dark:text-zinc-200">{{ $pem->user->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Alamat:</span>
                                <span class="text-zinc-800 dark:text-zinc-200 truncate max-w-[180px]">{{ $pem->alamat_instansi }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-12 text-center text-zinc-400 text-xs">Belum ada pemrakarsa usaha terdaftar.</div>
                @endforelse
            </div>
            @if ($pemrakarsaList->hasPages())
                <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    {{ $pemrakarsaList->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts::app>
