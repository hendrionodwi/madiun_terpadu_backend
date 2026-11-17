<x-layouts::app :title="__('Verifikasi Wilayah TPS3R')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $statusMeta = [
            'approved' => ['Disetujui', 'civic-status-success'],
            'pending' => ['Menunggu', 'civic-status-warning'],
            'rejected' => ['Ditolak', 'civic-status-danger'],
        ];
    @endphp

    <div class="civic-page mx-auto w-full max-w-7xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Wilayah TPS3R"
            eyebrow="Validasi Layanan"
            title="Verifikasi Wilayah Pengiriman"
            description="Tinjau cakupan layanan dan ongkir yang diajukan pengelola TPS3R."
            icon="map-pin"
        />

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <p class="font-bold">Keputusan belum disimpan:</p>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Menunggu</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ $statusCounts['pending'] ?? 0 }}</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Disetujui</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-civic-700 dark:text-civic-300">{{ $statusCounts['approved'] ?? 0 }}</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ditolak</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-red-700 dark:text-red-300">{{ $statusCounts['rejected'] ?? 0 }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard.admin.tps3r-delivery-zones.index') }}" class="civic-card mt-6 grid gap-3 p-4 md:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]">
            <label class="sr-only" for="zone-search">Cari TPS3R atau wilayah</label>
            <input id="zone-search" name="q" value="{{ $filters['q'] }}" class="{{ $fieldClass }}" placeholder="Cari TPS3R atau kelurahan">
            <select name="status" class="{{ $fieldClass }}" aria-label="Status">
                <option value="">Semua status</option>
                <option value="pending" @selected($filters['status'] === 'pending')>Menunggu</option>
                <option value="approved" @selected($filters['status'] === 'approved')>Disetujui</option>
                <option value="rejected" @selected($filters['status'] === 'rejected')>Ditolak</option>
            </select>
            <select name="kecamatan" class="{{ $fieldClass }}" aria-label="Kecamatan">
                <option value="">Semua kecamatan</option>
                @foreach (['Kartoharjo', 'Manguharjo', 'Taman'] as $district)
                    <option value="{{ $district }}" @selected($filters['kecamatan'] === $district)>{{ $district }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="civic-button-primary flex-1">Terapkan</button>
                <a href="{{ route('dashboard.admin.tps3r-delivery-zones.index') }}" class="civic-button-secondary" aria-label="Reset filter">Reset</a>
            </div>
        </form>

        <section class="mt-6 grid gap-4">
            @forelse ($deliveryZones as $zone)
                @php([$statusLabel, $statusClass] = $statusMeta[$zone->status])
                <article class="civic-card p-5 sm:p-6">
                    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.55fr)]">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                <span class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ $zone->public_id }}</span>
                            </div>
                            <h2 class="mt-3 font-display text-lg font-extrabold text-slate-950 dark:text-white">{{ $zone->tps3rProfile->nama_tps3r }}</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Kel. {{ $zone->kelurahan }}, Kec. {{ $zone->kecamatan }}</p>

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Ongkir armada</p>
                                    <p class="mt-1 font-display font-extrabold text-slate-950 dark:text-white">Rp{{ number_format($zone->delivery_fee, 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Pengelola</p>
                                    <p class="mt-1 truncate text-sm font-bold text-slate-950 dark:text-white">{{ $zone->tps3rProfile->user?->name ?? '-' }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Diajukan</p>
                                    <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">{{ $zone->created_at->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>

                            @if ($zone->rejection_reason)
                                <p class="mt-4 rounded-xl bg-red-50 px-3.5 py-3 text-xs text-red-800 dark:bg-red-950/40 dark:text-red-200">
                                    <strong>Alasan terakhir:</strong> {{ $zone->rejection_reason }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col justify-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                            @if ($zone->status !== 'approved')
                                <form method="POST" action="{{ route('dashboard.admin.tps3r-delivery-zones.review', $zone) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="civic-button-primary w-full">
                                        Setujui & aktifkan
                                    </button>
                                </form>
                            @endif

                            <details class="group">
                                <summary class="civic-button-secondary flex cursor-pointer list-none items-center justify-between gap-2">
                                    {{ $zone->status === 'approved' ? 'Cabut persetujuan' : 'Tolak pengajuan' }}
                                    <flux:icon.chevron-down class="size-4 transition group-open:rotate-180" />
                                </summary>
                                <form method="POST" action="{{ route('dashboard.admin.tps3r-delivery-zones.review', $zone) }}" class="mt-3 grid gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <textarea name="rejection_reason" class="{{ $fieldClass }} min-h-24 resize-y" maxlength="500" placeholder="Alasan penolakan" required></textarea>
                                    <button type="submit" class="min-h-11 rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                                        Simpan penolakan
                                    </button>
                                </form>
                            </details>

                            @if ($zone->approver)
                                <p class="text-center text-xs text-slate-500 dark:text-slate-400">Terakhir ditinjau {{ $zone->approver->name }}</p>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="civic-card flex min-h-64 flex-col items-center justify-center px-6 py-10 text-center">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-civic-50 text-civic-700 dark:bg-civic-950 dark:text-civic-300">
                        <flux:icon.check-circle class="size-6" />
                    </span>
                    <h2 class="mt-3 font-display font-extrabold text-slate-950 dark:text-white">Tidak ada pengajuan</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Coba ubah filter pencarian.</p>
                </div>
            @endforelse
        </section>

        @if ($deliveryZones->hasPages())
            <div class="mt-6">{{ $deliveryZones->links() }}</div>
        @endif
    </div>
</x-layouts::app>
