<x-layouts::app :title="__('Log Aktivitas Pengguna - Admin DLH')">
    <div class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Log Aktivitas</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Audit Trail & Log Aktivitas Sistem</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Pemantauan kronologis setiap transaksi, aksi verifikasi, dan interaksi pengguna dalam aplikasi.</p>
            </div>
        </div>

        <!-- Metric KPI Logs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Riwayat Log</span>
                <h3 class="text-2xl font-black text-zinc-900 dark:text-zinc-100 mt-2">{{ number_format($stats['total_logs']) }}</h3>
                <p class="text-xs text-zinc-400 mt-0.5">Pencatatan aktivitas audit trail</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Aktivitas Hari Ini</span>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($stats['today_logs']) }}</h3>
                <p class="text-xs text-zinc-400 mt-0.5">Interaksi sistem {{ now()->translatedFormat('d M Y') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Pengguna Terlibat</span>
                <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ number_format($stats['unique_users']) }}</h3>
                <p class="text-xs text-zinc-400 mt-0.5">Akun terdeteksi beraktivitas</p>
            </div>
        </div>

        <!-- Tabel Log Aktivitas -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden text-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 font-semibold uppercase">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Aktivitas / Aksi</th>
                            <th class="py-3 px-4">Method & Route</th>
                            <th class="py-3 px-4">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3.5 px-4 font-mono text-[11px] text-zinc-500">
                                    {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $log->user->name ?? 'System Guest' }}</p>
                                    <p class="text-[11px] text-zinc-400">{{ $log->user->email ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $log->action }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px]">
                                    <span class="px-1.5 py-0.5 rounded font-bold {{ $log->method === 'POST' ? 'bg-blue-100 text-blue-800' : ($log->method === 'PATCH' ? 'bg-amber-100 text-amber-800' : ($log->method === 'DELETE' ? 'bg-red-100 text-red-800' : 'bg-zinc-100 text-zinc-700')) }}">
                                        {{ $log->method }}
                                    </span>
                                    <span class="text-zinc-500 ml-1">{{ $log->path }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-zinc-400">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-zinc-400">
                                    Belum ada log aktivitas tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
