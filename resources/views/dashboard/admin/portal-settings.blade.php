<x-layouts::app :title="__('Tampilan Portal Publik')">
    @php
        $fieldClass = 'mt-1 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $labelClass = 'text-xs font-bold text-slate-700 dark:text-slate-300';
        $serviceSteps = old('service_steps', $portalSettings['service_steps'] ?? []);
        $visibilityOptions = [
            'announcement_banner_enabled' => ['Banner pengumuman', 'Tampilkan pengumuman aktif di bagian atas portal.'],
            'show_stats_section' => ['Statistik layanan', 'Tampilkan ringkasan data operasional publik.'],
            'show_education_section' => ['Artikel edukasi', 'Tampilkan artikel terbaru pada beranda.'],
            'show_marketplace_section' => ['Marketplace', 'Tampilkan produk sirkular pilihan.'],
        ];
    @endphp

    <div class="civic-page flex flex-col gap-5 p-2 md:p-4">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-civic-700 dark:text-civic-300">Konten publik</p>
                <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">Tampilan Portal</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Atur pesan utama, kontak, dan bagian yang ditampilkan.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="civic-button-secondary self-start sm:self-auto"><flux:icon.arrow-top-right-on-square class="size-4" /> Buka portal</a>
        </header>

        <x-admin.content-tabs :summary="$cmsSummary" />
        <x-admin.content-feedback />

        <form method="POST" action="{{ route('dashboard.admin.portal-settings.update') }}" class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_21rem]">
            @csrf
            @method('PATCH')

            <div class="space-y-5">
                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.rectangle-stack class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Bagian utama</h2><p class="text-xs text-slate-500">Teks yang pertama dilihat pengunjung.</p></div></div>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div><label for="hero_kicker" class="{{ $labelClass }}">Label kecil</label><input id="hero_kicker" name="hero_kicker" value="{{ old('hero_kicker', $portalSettings['hero_kicker']) }}" maxlength="120" class="{{ $fieldClass }}"></div>
                        <div><label for="operational_status_label" class="{{ $labelClass }}">Status layanan</label><input id="operational_status_label" name="operational_status_label" value="{{ old('operational_status_label', $portalSettings['operational_status_label']) }}" maxlength="120" class="{{ $fieldClass }}"></div>
                        <div class="lg:col-span-2"><label for="hero_title" class="{{ $labelClass }}">Judul utama</label><input id="hero_title" name="hero_title" value="{{ old('hero_title', $portalSettings['hero_title']) }}" maxlength="200" class="{{ $fieldClass }}"></div>
                        <div class="lg:col-span-2"><label for="hero_subtitle" class="{{ $labelClass }}">Deskripsi</label><textarea id="hero_subtitle" name="hero_subtitle" rows="4" maxlength="600" class="{{ $fieldClass }} py-3">{{ old('hero_subtitle', $portalSettings['hero_subtitle']) }}</textarea></div>
                        <div class="lg:col-span-2"><label for="hero_badge_text" class="{{ $labelClass }}">Sorotan singkat</label><input id="hero_badge_text" name="hero_badge_text" value="{{ old('hero_badge_text', $portalSettings['hero_badge_text']) }}" maxlength="200" class="{{ $fieldClass }}"></div>
                    </div>
                </section>

                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.eye class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Bagian beranda</h2><p class="text-xs text-slate-500">Aktifkan hanya informasi yang diperlukan.</p></div></div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($visibilityOptions as $name => [$title, $description])
                            <label class="flex min-h-20 cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-3 transition hover:border-civic-300 dark:border-slate-700">
                                <input type="hidden" name="{{ $name }}" value="0">
                                <input type="checkbox" name="{{ $name }}" value="1" @checked((string) old($name, $portalSettings[$name] ?? '0') === '1') class="mt-1 size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600">
                                <span><span class="block text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</span><span class="mt-1 block text-xs leading-5 text-slate-500">{{ $description }}</span></span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.list-bullet class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Alur layanan</h2><p class="text-xs text-slate-500">Maksimal delapan langkah.</p></div></div>
                    <div class="mt-5 grid gap-3 lg:grid-cols-2">
                        @foreach ($serviceSteps as $index => $step)
                            <fieldset class="rounded-2xl border border-slate-200 p-3 dark:border-slate-700">
                                <legend class="px-2 text-xs font-extrabold uppercase tracking-wider text-civic-700 dark:text-civic-300">Langkah {{ $loop->iteration }}</legend>
                                <input type="hidden" name="service_steps[{{ $index }}][step]" value="{{ $step['step'] ?? $loop->iteration }}">
                                <div><label class="{{ $labelClass }}">Judul</label><input name="service_steps[{{ $index }}][title]" value="{{ $step['title'] ?? '' }}" maxlength="100" required class="{{ $fieldClass }}"></div>
                                <div class="mt-3"><label class="{{ $labelClass }}">Deskripsi</label><textarea name="service_steps[{{ $index }}][description]" rows="3" maxlength="300" required class="{{ $fieldClass }} py-3">{{ $step['description'] ?? '' }}</textarea></div>
                            </fieldset>
                        @endforeach
                    </div>
                </section>

                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.building-office class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Kontak & transparansi</h2><p class="text-xs text-slate-500">Informasi resmi di bagian bawah portal.</p></div></div>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div class="lg:col-span-2"><label for="contact_address" class="{{ $labelClass }}">Alamat</label><input id="contact_address" name="contact_address" value="{{ old('contact_address', $portalSettings['contact_address']) }}" maxlength="255" class="{{ $fieldClass }}"></div>
                        <div><label for="contact_email" class="{{ $labelClass }}">Email</label><input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', $portalSettings['contact_email']) }}" maxlength="150" class="{{ $fieldClass }}"></div>
                        <div><label for="contact_phone" class="{{ $labelClass }}">Telepon</label><input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $portalSettings['contact_phone']) }}" maxlength="50" class="{{ $fieldClass }}"></div>
                        <div class="lg:col-span-2"><label for="transparency_note" class="{{ $labelClass }}">Catatan transparansi</label><textarea id="transparency_note" name="transparency_note" rows="3" maxlength="600" class="{{ $fieldClass }} py-3">{{ old('transparency_note', $portalSettings['transparency_note']) }}</textarea></div>
                    </div>
                </section>
            </div>

            <aside class="civic-card overflow-hidden xl:sticky xl:top-5">
                <div class="bg-gradient-to-br from-civic-950 via-civic-800 to-civic-700 p-5 text-white">
                    <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-xs font-bold">Pratinjau isi</span>
                    <h2 class="mt-4 font-display text-xl font-extrabold leading-tight">{{ old('hero_title', $portalSettings['hero_title']) }}</h2>
                    <p class="mt-3 text-sm leading-6 text-civic-50">{{ old('hero_subtitle', $portalSettings['hero_subtitle']) }}</p>
                </div>
                <div class="space-y-4 p-4">
                    <div><p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Status saat ini</p><p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ old('operational_status_label', $portalSettings['operational_status_label']) }}</p></div>
                    <div class="rounded-xl bg-civic-50 p-3 text-xs leading-5 text-civic-900 dark:bg-civic-950/40 dark:text-civic-100">Perubahan tampil setelah disimpan. Konten artikel dan produk tetap dikelola dari menu masing-masing.</div>
                    <button type="submit" class="civic-button-primary w-full"><flux:icon.check class="size-4" /> Simpan tampilan portal</button>
                </div>
            </aside>
        </form>
    </div>
</x-layouts::app>
