<x-layouts::app :title="__('Pengaturan Pengiriman - Admin DLH')">
    @php
        $selectedCouriers = old('couriers', $activeCourierCodes);
        $usesDatabase = $settingsSource === 'database';
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div class="civic-page mx-auto w-full max-w-6xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Pengaturan Pengiriman"
            eyebrow="Operasional Marketplace"
            title="Pengaturan Pengiriman"
            description="Tentukan kurir luar kota yang tersedia pada checkout web dan aplikasi mobile."
            icon="truck"
        />

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <p class="font-bold">Pengaturan belum disimpan:</p>
                <p class="mt-1 text-xs">{{ $errors->first() }}</p>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sumber pengaturan</p>
                <p class="mt-2 font-display text-lg font-extrabold text-slate-950 dark:text-white">
                    {{ $usesDatabase ? 'Database Admin' : 'Fallback .env' }}
                </p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kurir aktif</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-civic-700 dark:text-civic-300">{{ count($activeCourierCodes) }}</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cakupan</p>
                <p class="mt-2 font-display text-lg font-extrabold text-slate-950 dark:text-white">Web & Mobile</p>
            </div>
        </div>

        <form method="POST" action="{{ route('dashboard.admin.shipping-settings.store') }}" class="civic-card mt-6 p-5 sm:p-6">
            @csrf

            <div class="flex flex-col gap-1">
                <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Tambah kurir</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Gunakan kode kurir persis seperti yang tercantum pada RajaOngkir/Komerce.</p>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-[12rem_16rem_minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <label for="courier-code" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Kode API</label>
                    <input id="courier-code" name="code" value="{{ old('code') }}" class="{{ $fieldClass }} font-mono lowercase" maxlength="50" placeholder="contoh: tiki" required>
                </div>
                <div>
                    <label for="courier-name" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Nama kurir</label>
                    <input id="courier-name" name="name" value="{{ old('name') }}" class="{{ $fieldClass }}" maxlength="100" placeholder="TIKI" required>
                </div>
                <div>
                    <label for="courier-description" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Deskripsi singkat</label>
                    <input id="courier-description" name="description" value="{{ old('description') }}" class="{{ $fieldClass }}" maxlength="500" placeholder="Layanan pengiriman yang tersedia melalui RajaOngkir">
                </div>
                <button type="submit" class="civic-button-primary min-h-11 shrink-0">Tambah kurir</button>
            </div>
        </form>

        <form method="POST" action="{{ route('dashboard.admin.shipping-settings.update') }}" class="civic-card mt-4 overflow-hidden">
            @csrf
            @method('PATCH')

            <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">
                <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Kurir luar kota</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Hanya kurir yang dicentang yang diminta ke RajaOngkir dan ditampilkan saat checkout.</p>
            </div>

            <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
                @foreach ($couriers as $courier)
                    <article class="relative min-h-28 overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-civic-300 has-checked:border-civic-500 has-checked:bg-civic-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-civic-700 dark:has-checked:border-civic-600 dark:has-checked:bg-civic-950/60">
                        <label class="flex h-full cursor-pointer gap-3 p-4 pr-14">
                            <input
                                type="checkbox"
                                name="couriers[]"
                                value="{{ $courier['code'] }}"
                                @checked(in_array($courier['code'], $selectedCouriers, true))
                                class="mt-0.5 size-5 shrink-0 rounded border-slate-300 text-civic-700 focus:ring-civic-500 dark:border-slate-600 dark:bg-slate-950"
                            >
                            <span class="min-w-0">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="font-display font-extrabold text-slate-950 dark:text-white">{{ $courier['name'] }}</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 font-mono text-[0.65rem] font-bold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $courier['code'] }}</span>
                                </span>
                                <span class="mt-2 block text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $courier['description'] ?: 'Tanpa deskripsi tambahan.' }}</span>
                            </span>
                        </label>
                        <button
                            type="submit"
                            form="delete-courier-{{ $courier['code'] }}"
                            class="absolute top-3 right-3 flex size-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-200 dark:hover:bg-red-950/50 dark:hover:text-red-300"
                            aria-label="Hapus {{ $courier['name'] }}"
                            title="Hapus kurir"
                            onclick="return confirm('Hapus kurir ini dari katalog?')"
                        >
                            <flux:icon.trash class="size-4" />
                        </button>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/70 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih minimal satu kurir. Perubahan langsung berlaku untuk permintaan ongkir berikutnya.</p>
                <button type="submit" class="civic-button-primary shrink-0">Simpan pengaturan</button>
            </div>
        </form>

        @foreach ($couriers as $courier)
            <form id="delete-courier-{{ $courier['code'] }}" method="POST" action="{{ route('dashboard.admin.shipping-settings.destroy', $courier['code']) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
</x-layouts::app>
