<x-layouts::app :title="__('Log Aktivitas Pengguna - Admin DLH')">
    <div class="civic-page">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Log Aktivitas"
            eyebrow="Audit Sistem"
            title="Audit Trail & Log Aktivitas Sistem"
            description="Pemantauan kronologis setiap transaksi, aksi verifikasi, dan interaksi pengguna dalam aplikasi."
            icon="clock"
        />

        <!-- Metric KPI Logs -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Riwayat Log</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-slate-950 dark:text-white">{{ number_format($stats['total_logs']) }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pencatatan aktivitas audit trail</p>
            </div>
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aktivitas Hari Ini</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-civic-700 dark:text-civic-300">{{ number_format($stats['today_logs']) }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Interaksi sistem {{ now()->translatedFormat('d M Y') }}</p>
            </div>
            <div class="civic-stat-card">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pengguna Terlibat</span>
                <h3 class="mt-2 font-display text-2xl font-extrabold text-sky-700 dark:text-sky-400">{{ number_format($stats['unique_users']) }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Akun terdeteksi beraktivitas</p>
            </div>
        </div>

        <!-- Tabel Log Aktivitas -->
        <div data-ajax-zone="admin-activity-logs" class="civic-card overflow-hidden">
            <div class="civic-table-wrap rounded-none border-0">
                <table class="civic-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aktivitas / Aksi</th>
                            <th>Method & Route</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="font-mono text-xs text-slate-500 dark:text-slate-400">
                                    {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                                </td>
                                <td>
                                    <p class="font-bold text-slate-950 dark:text-white">{{ $log->user->name ?? 'System Guest' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $log->user->email ?? '-' }}</p>
                                </td>
                                <td>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $log->action }}</span>
                                </td>
                                <td class="font-mono text-xs">
                                    <span class="rounded px-1.5 py-0.5 font-bold {{ $log->method === 'POST' ? 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300' : ($log->method === 'PATCH' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : ($log->method === 'DELETE' ? 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300')) }}">
                                        {{ $log->method }}
                                    </span>
                                    <span class="ml-1 text-slate-500 dark:text-slate-400">{{ $log->path }}</span>
                                </td>
                                <td class="font-mono text-xs text-slate-500 dark:text-slate-400">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada log aktivitas tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
