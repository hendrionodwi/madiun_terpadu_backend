<x-layouts::app :title="__('Wilayah Pengiriman TPS3R')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $statusMeta = [
            'approved' => ['Disetujui', 'civic-status-success'],
            'pending' => ['Menunggu verifikasi', 'civic-status-warning'],
            'rejected' => ['Ditolak', 'civic-status-danger'],
        ];
    @endphp

    <div class="civic-page mx-auto w-full max-w-7xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Wilayah Pengiriman"
            eyebrow="Pengaturan Marketplace"
            title="Wilayah Pengiriman"
            description="Tentukan kelurahan yang dilayani dan ongkir armada TPS3R."
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
                <p class="font-bold">Periksa data wilayah:</p>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Wilayah disetujui</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-civic-700 dark:text-civic-300">{{ $statusCounts['approved'] }}</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Menunggu admin</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ $statusCounts['pending'] }}</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Perlu perbaikan</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-red-700 dark:text-red-300">{{ $statusCounts['rejected'] }}</p>
            </div>
        </div>

        <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,0.78fr)_minmax(0,1.22fr)]">
            <section class="civic-card p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700 dark:bg-civic-950 dark:text-civic-300">
                        <flux:icon.plus class="size-5" />
                    </span>
                    <div>
                        <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Tambah wilayah</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pengajuan aktif setelah disetujui Admin DLH.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('dashboard.tps3r.delivery-zones.store') }}" class="mt-5 grid gap-4">
                    @csrf
                    <label class="grid gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Kecamatan
                        <span class="relative">
                            <select name="kecamatan" class="{{ $fieldClass }} appearance-none pe-10" required>
                                <option value="">Pilih kecamatan</option>
                                @foreach (['Kartoharjo', 'Manguharjo', 'Taman'] as $district)
                                    <option value="{{ $district }}" @selected(old('kecamatan') === $district)>{{ $district }}</option>
                                @endforeach
                            </select>
                            <flux:icon.chevron-down class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                        </span>
                    </label>

                    <label class="grid gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Kelurahan
                        <input name="kelurahan" value="{{ old('kelurahan') }}" class="{{ $fieldClass }}" maxlength="100" placeholder="Contoh: Oro-Oro Ombo" required>
                    </label>

                    <label class="grid gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Ongkir armada
                        <span class="relative">
                            <span class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-500">Rp</span>
                            <input type="number" name="delivery_fee" value="{{ old('delivery_fee', 0) }}" class="{{ $fieldClass }} ps-10" min="0" max="1000000" step="1000" required>
                        </span>
                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400">Gunakan Rp0 untuk pengiriman gratis.</span>
                    </label>

                    <input type="hidden" name="is_active" value="1">
                    <button type="submit" class="civic-button-primary w-full">
                        Ajukan wilayah
                    </button>
                </form>
            </section>

            <section class="civic-card overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:px-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Cakupan {{ $profile->nama_tps3r }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $deliveryZones->count() }} wilayah terdaftar</p>
                        </div>
                        <span class="civic-status-info">Marketplace lokal</span>
                    </div>
                </div>

                @forelse ($deliveryZones as $zone)
                    @php([$statusLabel, $statusClass] = $statusMeta[$zone->status])
                    <article class="border-b border-slate-200 p-5 last:border-b-0 dark:border-slate-800 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Kel. {{ $zone->kelurahan }}</h3>
                                    <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                    @if ($zone->status === 'approved')
                                        <span class="{{ $zone->is_active ? 'civic-status-success' : 'civic-status-info' }}">
                                            {{ $zone->is_active ? 'Melayani' : 'Nonaktif' }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Kec. {{ $zone->kecamatan }} · <strong>Rp{{ number_format($zone->delivery_fee, 0, ',', '.') }}</strong></p>
                                @if ($zone->rejection_reason)
                                    <p class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-800 dark:bg-red-950/40 dark:text-red-200">
                                        {{ $zone->rejection_reason }}
                                    </p>
                                @endif
                            </div>

                            @if ($zone->status === 'approved')
                                <form method="POST" action="{{ route('dashboard.tps3r.delivery-zones.update', $zone) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $zone->is_active ? 0 : 1 }}">
                                    <button type="submit" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                                        {{ $zone->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        <details class="group mt-4 rounded-xl border border-slate-200 bg-slate-50/70 open:bg-white dark:border-slate-800 dark:bg-slate-900/70 dark:open:bg-slate-950">
                            <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 px-3.5 py-2.5 text-sm font-bold text-civic-700 dark:text-civic-300">
                                Edit wilayah
                                <flux:icon.chevron-down class="size-4 transition group-open:rotate-180" />
                            </summary>
                            <div class="border-t border-slate-200 p-3.5 dark:border-slate-800">
                                <form method="POST" action="{{ route('dashboard.tps3r.delivery-zones.update', $zone) }}" class="grid gap-3 sm:grid-cols-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="kecamatan" class="{{ $fieldClass }}" aria-label="Kecamatan" required>
                                        @foreach (['Kartoharjo', 'Manguharjo', 'Taman'] as $district)
                                            <option value="{{ $district }}" @selected($zone->kecamatan === $district)>{{ $district }}</option>
                                        @endforeach
                                    </select>
                                    <input name="kelurahan" value="{{ $zone->kelurahan }}" class="{{ $fieldClass }}" aria-label="Kelurahan" required>
                                    <input type="number" name="delivery_fee" value="{{ (int) $zone->delivery_fee }}" class="{{ $fieldClass }}" min="0" max="1000000" step="1000" aria-label="Ongkir" required>
                                    <input type="hidden" name="is_active" value="{{ $zone->is_active ? 1 : 0 }}">
                                    <button type="submit" class="civic-button-primary">Simpan perubahan</button>
                                </form>

                                @if ($zone->status !== 'approved')
                                    <form method="POST" action="{{ route('dashboard.tps3r.delivery-zones.destroy', $zone) }}" class="mt-3" onsubmit="return confirm('Hapus pengajuan wilayah ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="min-h-10 text-xs font-bold text-red-700 hover:underline dark:text-red-300">Hapus pengajuan</button>
                                    </form>
                                @endif
                            </div>
                        </details>
                    </article>
                @empty
                    <div class="flex min-h-56 flex-col items-center justify-center px-6 py-10 text-center">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-civic-50 text-civic-700 dark:bg-civic-950 dark:text-civic-300">
                            <flux:icon.map-pin class="size-6" />
                        </span>
                        <h3 class="mt-3 font-display font-extrabold text-slate-950 dark:text-white">Belum ada wilayah</h3>
                        <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Tambahkan kelurahan yang dapat dijangkau armada TPS3R.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-layouts::app>
