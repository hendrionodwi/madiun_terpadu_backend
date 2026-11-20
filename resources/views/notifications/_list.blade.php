<section class="mx-auto w-full max-w-4xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-civic-700 dark:text-civic-300">Pusat notifikasi</p>
            <h1 class="mt-1 font-display text-3xl font-black tracking-tight text-slate-950 dark:text-white">Aktivitas transaksi</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Pembaruan pesanan marketplace untuk pembeli dan pengelola TPS3R.</p>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 transition hover:bg-civic-50 dark:border-civic-800 dark:bg-slate-900 dark:text-civic-200 dark:hover:bg-civic-950">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = $notification->read_at === null;
                $isCancelled = ($data['status'] ?? null) === 'dibatalkan';
                $isCompleted = ($data['status'] ?? null) === 'selesai';
            @endphp
            <article @class([
                'relative overflow-hidden rounded-2xl border p-4 shadow-sm transition sm:p-5',
                'border-civic-200 bg-civic-50/70 dark:border-civic-800 dark:bg-civic-950/35' => $isUnread,
                'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' => ! $isUnread,
            ])>
                @if ($isUnread)
                    <span class="absolute inset-y-0 left-0 w-1 bg-civic-600" aria-hidden="true"></span>
                @endif

                <div class="flex gap-3 sm:gap-4">
                    <span @class([
                        'flex size-11 shrink-0 items-center justify-center rounded-xl',
                        'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' => $isCancelled,
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $isCompleted,
                        'bg-civic-100 text-civic-700 dark:bg-civic-950 dark:text-civic-300' => ! $isCancelled && ! $isCompleted,
                    ])>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M3 6h18M5 6l1 14h12l1-14M8 6V4h8v2M9 10v6M15 10v6" />
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $data['title'] ?? 'Pembaruan transaksi' }}</h2>
                                    @if ($isUnread)
                                        <span class="rounded-full bg-civic-700 px-2 py-0.5 text-[0.65rem] font-extrabold uppercase tracking-wide text-white">Baru</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $data['message'] ?? '' }}</p>
                            </div>
                            <time class="shrink-0 text-xs font-semibold text-slate-400" datetime="{{ $notification->created_at?->toIso8601String() }}">
                                {{ $notification->created_at?->diffForHumans() }}
                            </time>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap gap-2 text-xs font-bold">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $data['order_number'] ?? '-' }}</span>
                                <span class="rounded-lg bg-white px-2.5 py-1 text-civic-800 ring-1 ring-civic-200 dark:bg-slate-950 dark:text-civic-200 dark:ring-civic-800">{{ $data['status_label'] ?? ucfirst($data['status'] ?? '-') }}</span>
                            </div>

                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="open" value="1">
                                <button type="submit" class="inline-flex min-h-9 items-center gap-1.5 rounded-xl bg-civic-700 px-3.5 text-xs font-extrabold text-white transition hover:bg-civic-800">
                                    Lihat pesanan
                                    <span aria-hidden="true">→</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900">
                <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-civic-50 text-civic-700 dark:bg-civic-950 dark:text-civic-300">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></svg>
                </span>
                <h2 class="mt-4 font-display text-lg font-extrabold text-slate-950 dark:text-white">Belum ada notifikasi</h2>
                <p class="mt-1 text-sm text-slate-500">Pembaruan transaksi berikutnya akan tampil di sini.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</section>
