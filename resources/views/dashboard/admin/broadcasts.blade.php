<x-layouts::app :title="__('Siaran Pengumuman & Broadcast - Admin DLH')">
    <div class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Siaran Pengumuman</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Sistem Siaran Pengumuman & Broadcast</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Kirimkan notifikasi dan pengumuman massal kepada warga, petugas lapangan, atau pelaku usaha Kota Madiun.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Publikasi Siaran Pengumuman Baru -->
            <div class="lg:col-span-1 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm h-fit text-xs">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-1">Publikasi Siaran Baru</h2>
                <p class="text-zinc-500 mb-4">Pengumuman akan tampil di portal warga dan aplikasi.</p>

                <form method="POST" action="{{ route('dashboard.admin.broadcasts.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Judul Siaran *</label>
                        <input type="text" name="title" placeholder="Jadwal Penjemputan Khusus Hari Raya Idul Fitri" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Target Audiens *</label>
                            <select name="audience_role" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-semibold">
                                <option value="all">Semua Pengguna</option>
                                <option value="warga">Masyarakat / Warga</option>
                                <option value="petugas_lapangan">Petugas Lapangan</option>
                                <option value="pengelola_tps3r">Pengelola TPS3R</option>
                                <option value="pemrakarsa">Pemrakarsa Usaha</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Prioritas *</label>
                            <select name="priority" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-semibold">
                                <option value="normal">Normal</option>
                                <option value="high">Penting (Tinggi)</option>
                                <option value="urgent">Mendesak (Darurat)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Isi Pesan Pengumuman *</label>
                        <textarea name="content" rows="4" placeholder="Tuliskan isi pengumuman, himbauan pemilahan, atau penyesuaian jadwal operasional..." required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"></textarea>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 font-bold text-white shadow-sm transition hover:bg-emerald-700">
                        <flux:icon.megaphone class="size-4" />
                        Publikasikan Siaran Sekarang
                    </button>
                </form>
            </div>

            <!-- Tabel Riwayat Siaran Pengumuman -->
            <div class="lg:col-span-2 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden text-xs">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Riwayat Siaran Pengumuman</h2>
                        <p class="text-xs text-zinc-500">Daftar notifikasi yang telah disiarkan ke pengguna.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                        {{ $broadcasts->total() }} Pengumuman
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 font-semibold uppercase">
                                <th class="py-3 px-4">Judul & Isi Pengumuman</th>
                                <th class="py-3 px-4">Target Audiens</th>
                                <th class="py-3 px-4">Prioritas</th>
                                <th class="py-3 px-4">Rilis</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($broadcasts as $bc)
                                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                                    <td class="py-3.5 px-4 max-w-sm">
                                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $bc->title }}</h4>
                                        <p class="text-zinc-500 text-[11px] mt-0.5 line-clamp-2 leading-relaxed">{{ $bc->content }}</p>
                                        <span class="text-[10px] text-zinc-400 mt-1 block">Oleh: {{ $bc->creator->name ?? 'Admin DLH' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold">
                                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                            {{ $bc->audience_role === 'all' ? 'Semua Pengguna' : ucfirst(str_replace('_', ' ', $bc->audience_role)) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $bc->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' :
                                               ($bc->priority === 'high' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300') }}">
                                            {{ $bc->priority }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-500 text-[11px]">
                                        {{ $bc->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $bc->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-700' }}">
                                            <span class="size-1.5 rounded-full {{ $bc->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                                            {{ $bc->is_active ? 'Aktif' : 'Arsip' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-zinc-400">
                                        Belum ada siaran pengumuman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($broadcasts->hasPages())
                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                        {{ $broadcasts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
