<x-layouts::app :title="__('Tampilan Portal Publik')">
    @php
        $fieldClass = 'mt-1 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $labelClass = 'text-xs font-bold text-slate-700 dark:text-slate-300';
        $serviceSteps = old('service_steps', $portalSettings['service_steps'] ?? []);
        $visibilityOptions = [
            'announcement_banner_enabled' => ['Banner pengumuman', 'Tampilkan pengumuman aktif di bagian atas portal.'],
            'show_banner_slider' => ['Slider banner pengumuman & edukasi', 'Tampilkan carousel banner dinamis untuk pengumuman resmi dan program edukasi.'],
            'show_app_download_section' => ['Seksi unduh aplikasi & QR Code', 'Tampilkan seksi unduh aplikasi mobile warga beserta QR Code interaktif.'],
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
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Atur pesan utama, foto latar belakang hero, unduhan aplikasi mobile, QR Code, dan visibilitas seksi portal.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="civic-button-secondary self-start sm:self-auto"><flux:icon.arrow-top-right-on-square class="size-4" /> Buka portal</a>
        </header>

        <x-admin.content-tabs :summary="$cmsSummary" />
        <x-admin.content-feedback />

        <form method="POST" action="{{ route('dashboard.admin.portal-settings.update') }}" enctype="multipart/form-data" class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_21rem]">
            @csrf
            @method('PATCH')

            <div class="space-y-5">
                {{-- Bagian Utama (Teks Hero) --}}
                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.rectangle-stack class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Bagian utama (Hero)</h2><p class="text-xs text-slate-500">Teks headline dan deskripsi pembuka di beranda.</p></div></div>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div><label for="hero_kicker" class="{{ $labelClass }}">Label kecil</label><input id="hero_kicker" name="hero_kicker" value="{{ old('hero_kicker', $portalSettings['hero_kicker']) }}" maxlength="120" class="{{ $fieldClass }}"></div>
                        <div><label for="operational_status_label" class="{{ $labelClass }}">Status layanan</label><input id="operational_status_label" name="operational_status_label" value="{{ old('operational_status_label', $portalSettings['operational_status_label']) }}" maxlength="120" class="{{ $fieldClass }}"></div>
                        <div class="lg:col-span-2"><label for="hero_title" class="{{ $labelClass }}">Judul utama</label><input id="hero_title" name="hero_title" value="{{ old('hero_title', $portalSettings['hero_title']) }}" maxlength="200" class="{{ $fieldClass }}"></div>
                        <div class="lg:col-span-2"><label for="hero_subtitle" class="{{ $labelClass }}">Deskripsi</label><textarea id="hero_subtitle" name="hero_subtitle" rows="3" maxlength="600" class="{{ $fieldClass }} py-3">{{ old('hero_subtitle', $portalSettings['hero_subtitle']) }}</textarea></div>
                    </div>
                </section>

                {{-- Visual Latar Belakang & Tombol Aksi Hero --}}
                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.photo class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Visual Latar Belakang & Tombol Hero</h2><p class="text-xs text-slate-500">Atur foto background layar penuh hero dan tombol aksi warga.</p></div></div>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        {{-- Foto Background Hero --}}
                        <div class="lg:col-span-2">
                            <label class="{{ $labelClass }}">Foto Latar Belakang Hero (Background Banner Penuh)</label>
                            <p class="text-[11px] text-slate-500 mb-2">Foto lanskap Kota Madiun, landmark, atau aktivitas lingkungan (rasio 16:9 disarankan, format PNG/JPG/WebP, maks. 5MB). Bagian bawah foto akan otomatis diberikan efek blur dan pudar (*fade-out*) menyatu halus dengan warna palet di bawahnya.</p>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                @if (!empty($portalSettings['hero_background_image']))
                                    <div class="relative shrink-0">
                                        <img src="{{ Storage::url($portalSettings['hero_background_image']) }}" alt="Hero Background" class="h-24 w-40 rounded-xl border border-slate-200 object-cover shadow-xs dark:border-slate-700">
                                    </div>
                                @else
                                    <div class="flex h-24 w-40 shrink-0 flex-col items-center justify-center rounded-xl border border-dashed border-emerald-300 bg-emerald-50/50 p-2 text-center dark:border-emerald-800 dark:bg-emerald-950/20">
                                        <flux:icon.photo class="size-6 text-emerald-600 dark:text-emerald-400" />
                                        <span class="mt-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">Foto Bawaan Kota Aktif</span>
                                    </div>
                                @endif
                                <div class="flex-1 space-y-2">
                                    <input type="file" name="hero_background_image" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-civic-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-civic-800 hover:file:bg-civic-200 dark:file:bg-civic-900/60 dark:file:text-civic-200">
                                    @if (!empty($portalSettings['hero_background_image']))
                                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                            <input type="checkbox" name="remove_hero_background_image" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                            <span>Hapus foto latar kustom & gunakan latar bawaan Kota Madiun</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="hero_cta_primary_text" class="{{ $labelClass }}">Teks Tombol Utama</label>
                            <input id="hero_cta_primary_text" name="hero_cta_primary_text" value="{{ old('hero_cta_primary_text', $portalSettings['hero_cta_primary_text'] ?? 'Unduh Aplikasi Warga') }}" maxlength="60" class="{{ $fieldClass }}" placeholder="Contoh: Unduh Aplikasi Warga">
                        </div>
                        <div>
                            <label for="hero_cta_primary_link" class="{{ $labelClass }}">Tautan Tombol Utama</label>
                            <input id="hero_cta_primary_link" name="hero_cta_primary_link" value="{{ old('hero_cta_primary_link', $portalSettings['hero_cta_primary_link'] ?? '#unduh-aplikasi') }}" maxlength="255" class="{{ $fieldClass }}" placeholder="Contoh: #unduh-aplikasi atau URL Play Store">
                        </div>
                        <div>
                            <label for="hero_cta_secondary_text" class="{{ $labelClass }}">Teks Tombol Sekunder</label>
                            <input id="hero_cta_secondary_text" name="hero_cta_secondary_text" value="{{ old('hero_cta_secondary_text', $portalSettings['hero_cta_secondary_text'] ?? 'Jadwal & Peta Armada') }}" maxlength="60" class="{{ $fieldClass }}" placeholder="Contoh: Jadwal & Peta Armada">
                        </div>
                        <div>
                            <label for="hero_cta_secondary_link" class="{{ $labelClass }}">Tautan Tombol Sekunder</label>
                            <input id="hero_cta_secondary_link" name="hero_cta_secondary_link" value="{{ old('hero_cta_secondary_link', $portalSettings['hero_cta_secondary_link'] ?? '/layanan') }}" maxlength="255" class="{{ $fieldClass }}" placeholder="Contoh: /layanan">
                        </div>
                    </div>
                </section>

                {{-- Seksi Unduh Aplikasi Mobile & QR Code Dinamis --}}
                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.qr-code class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Aplikasi Mobile & QR Code Warga</h2><p class="text-xs text-slate-500">Atur konten ajakan unduh dan tautan QR Code yang otomatis digenerate.</p></div></div>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label for="app_download_title" class="{{ $labelClass }}">Judul Seksi Unduh Aplikasi</label>
                            <input id="app_download_title" name="app_download_title" value="{{ old('app_download_title', $portalSettings['app_download_title'] ?? 'Semua Urusan Sampah Lingkungan Selesai Lewat Ponsel') }}" maxlength="150" class="{{ $fieldClass }}">
                        </div>
                        <div class="lg:col-span-2">
                            <label for="app_download_subtitle" class="{{ $labelClass }}">Deskripsi Aplikasi</label>
                            <textarea id="app_download_subtitle" name="app_download_subtitle" rows="3" maxlength="600" class="{{ $fieldClass }} py-3">{{ old('app_download_subtitle', $portalSettings['app_download_subtitle'] ?? 'Aplikasi mobile Madiun Terpadu dirancang khusus untuk memudahkan warga memantau armada keliling, mengajukan jadwal penjemputan berkala, dan mencatat timbangan sampah langsung di depan rumah.') }}</textarea>
                        </div>
                        <div>
                            <label for="app_download_button_text" class="{{ $labelClass }}">Teks Tombol Unduh</label>
                            <input id="app_download_button_text" name="app_download_button_text" value="{{ old('app_download_button_text', $portalSettings['app_download_button_text'] ?? 'Unduh APK Android (Gratis)') }}" maxlength="60" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label for="app_download_badge" class="{{ $labelClass }}">Lencana Keterangan / Versi</label>
                            <input id="app_download_badge" name="app_download_badge" value="{{ old('app_download_badge', $portalSettings['app_download_badge'] ?? 'Android 8.0+ · Gratis & Resmi DLH Kota Madiun') }}" maxlength="100" class="{{ $fieldClass }}">
                        </div>
                        <div class="lg:col-span-2">
                            <label for="app_download_url" class="{{ $labelClass }}">Tautan Unduh Target (Yang Di-scan oleh QR Code)</label>
                            <input id="app_download_url" name="app_download_url" value="{{ old('app_download_url', $portalSettings['app_download_url'] ?? '') }}" maxlength="500" class="{{ $fieldClass }}" placeholder="Contoh: /madiun-terpadu-release.apk atau URL Google Play Store">
                            <p class="mt-1 text-[11px] text-slate-500">Kosongkan sampai APK atau tautan resmi tersedia. QR Code hanya akan aktif ketika tautan ini sudah diisi.</p>
                        </div>
                        <div class="lg:col-span-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <label class="{{ $labelClass }}">Gambar QR Code Kustom (Opsional)</label>
                            <p class="text-[11px] text-slate-500 mb-2">Kosongkan untuk menggunakan QR Code dinamis otomatis dari tautan di atas. Unggah di sini jika Anda memiliki gambar QR berlogo resmi DLH khusus (format PNG, JPG, WebP, maks. 3MB).</p>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                @if (!empty($portalSettings['app_qr_custom_image']))
                                    <div class="relative shrink-0">
                                        <img src="{{ Storage::url($portalSettings['app_qr_custom_image']) }}" alt="Custom QR" class="size-20 rounded-xl border border-slate-200 object-contain shadow-xs dark:border-slate-700">
                                    </div>
                                @else
                                    <div class="flex size-20 shrink-0 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-2 text-center dark:border-slate-700 dark:bg-slate-800/40">
                                        <flux:icon.qr-code class="size-6 text-slate-400" />
                                        <span class="mt-1 text-[9px] font-bold text-slate-500">Auto Generate</span>
                                    </div>
                                @endif
                                <div class="flex-1 space-y-2">
                                    <input type="file" name="app_qr_custom_image" accept="image/png,image/jpeg,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-civic-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-civic-800 hover:file:bg-civic-200 dark:file:bg-civic-900/60 dark:file:text-civic-200">
                                    @if (!empty($portalSettings['app_qr_custom_image']))
                                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                            <input type="checkbox" name="remove_app_qr_custom_image" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                            <span>Hapus gambar QR kustom & gunakan QR Code generate otomatis</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Bagian Beranda (Visibilitas Seksi) --}}
                <section class="civic-card p-4 sm:p-5">
                    <div class="flex items-center gap-3"><span class="civic-icon-tile"><flux:icon.eye class="size-5" /></span><div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Bagian beranda</h2><p class="text-xs text-slate-500">Aktifkan atau sembunyikan seksi di beranda publik.</p></div></div>
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
