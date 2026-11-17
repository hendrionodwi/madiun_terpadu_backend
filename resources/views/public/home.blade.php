@extends('layouts.public', ['title' => 'Portal Publik'])

@section('content')
    {{-- HERO SECTION (Background Foto Dinamis Penuh & Efek Fade Bawah - Inspirasi JAKI × Civic Emerald) --}}
    <section class="relative overflow-hidden border-b border-civic-200/80 bg-slate-900 py-12 sm:py-16 lg:py-20">
        {{-- Background Foto Dinamis (bisa dikonfigurasi via CMS Admin) --}}
        <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
            @if (!empty($portalSettings['hero_background_image']))
                <img
                    src="{{ Storage::url($portalSettings['hero_background_image']) }}"
                    alt="Background Kota Madiun"
                    class="h-full w-full object-cover object-center"
                >
            @else
                {{-- Foto Default Pemandangan Smart City / Kota Madiun --}}
                <img
                    src="https://images.unsplash.com/photo-1519501025264-65ba15a82390?auto=format&fit=crop&w=2000&q=80"
                    alt="Background Kota Madiun"
                    class="h-full w-full object-cover object-center"
                >
            @endif

            {{-- Lapisan overlay halus agar teks & kontrol pencarian tetap sangat kontras dan terbaca --}}
            <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/88 to-white/65 backdrop-blur-[1px]"></div>

            {{-- Ambient decorative glow --}}
            <div class="absolute -right-24 top-0 h-96 w-96 rounded-full bg-emerald-400/20 blur-3xl" aria-hidden="true"></div>
            <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-civic-300/25 blur-3xl" aria-hidden="true"></div>

            {{-- Efek Blur & Fade Out Bawah: Foto pudar menghilang & blur menyatu mulus ke warna palet website --}}
            <div class="absolute inset-x-0 bottom-0 h-36 sm:h-48 bg-gradient-to-b from-transparent via-white/80 to-white backdrop-blur-[2px]"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8 lg:py-16">
            <div class="portal-settle max-w-3xl">
                {{-- Kicker Badge --}}
                <div class="inline-flex items-center gap-2 rounded-full border border-civic-200/90 bg-white/95 px-3.5 py-1 text-xs font-bold text-civic-800 shadow-2xs backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-600"></span>
                    </span>
                    {{ $portalSettings['hero_kicker'] ?? 'Portal Layanan Terpadu DLH Kota Madiun' }}
                </div>

                {{-- Headline --}}
                <h1 class="mt-5 font-display text-3xl font-extrabold leading-[1.15] tracking-[-0.03em] text-slate-950 sm:text-4xl lg:text-5xl">
                    {{ $portalSettings['hero_title'] }}
                </h1>

                {{-- Subtitle --}}
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                    {{ $portalSettings['hero_subtitle'] }}
                </p>

                {{-- Quick Search Bar --}}
                <form id="cari-layanan" method="GET" action="{{ route('public.services') }}" class="mt-7 flex max-w-xl flex-col gap-2 rounded-2xl border border-civic-200 bg-white p-2 shadow-sm transition focus-within:border-civic-500 focus-within:ring-4 focus-within:ring-civic-100 sm:flex-row sm:items-center">
                    <label class="relative flex flex-1 items-center">
                        <svg class="pointer-events-none ml-3.5 size-4.5 shrink-0 text-civic-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <input
                            id="portal-search-input"
                            type="text"
                            name="search"
                            placeholder="Cari nama TPS3R, kelurahan, atau rute..."
                            class="min-h-11 w-full border-0 bg-transparent px-3 text-sm text-slate-900 outline-none placeholder:text-slate-400"
                        >
                    </label>
                    <button type="submit" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-civic-700 px-5 text-sm font-bold text-white hover:bg-civic-800">
                        Cari layanan
                    </button>
                </form>

                {{-- Shortcut Wilayah Kecamatan --}}
                <div class="mt-3.5 flex flex-wrap items-center gap-2 text-xs">
                    <span class="font-semibold text-slate-500">Kecamatan:</span>
                    @foreach (['Kartoharjo', 'Manguharjo', 'Taman'] as $districtShortcut)
                        <a href="{{ route('public.services', ['district' => $districtShortcut]) }}" class="rounded-full border border-civic-200 bg-white px-3 py-1 font-semibold text-civic-800 transition hover:border-civic-400 hover:bg-civic-50">
                            Kec. {{ $districtShortcut }}
                        </a>
                    @endforeach
                </div>

                {{-- Tombol Aksi Utama (CTA) --}}
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ $portalSettings['hero_cta_primary_link'] ?? '#unduh-aplikasi' }}" class="group inline-flex min-h-12 items-center gap-2.5 rounded-xl bg-civic-700 px-6 text-sm font-bold text-white shadow-md shadow-civic-700/20 transition hover:-translate-y-0.5 hover:bg-civic-800">
                        <flux:icon.device-phone-mobile class="size-5 shrink-0" />
                        <span>{{ $portalSettings['hero_cta_primary_text'] ?? 'Unduh Aplikasi Warga' }}</span>
                    </a>
                    <a href="{{ $portalSettings['hero_cta_secondary_link'] ?? route('public.services') }}" class="inline-flex min-h-12 items-center gap-2 rounded-xl border border-civic-200 bg-white px-5 text-sm font-bold text-civic-800 shadow-2xs transition hover:border-civic-400 hover:bg-civic-50">
                        <flux:icon.map class="size-4 shrink-0 text-civic-700" />
                        <span>{{ $portalSettings['hero_cta_secondary_text'] ?? 'Jadwal & Peta Armada' }}</span>
                    </a>
                    <a href="{{ route('public.marketplace') }}" class="inline-flex min-h-12 items-center gap-1.5 rounded-xl px-4 text-sm font-bold text-slate-700 hover:text-civic-700">
                        <flux:icon.shopping-bag class="size-4 shrink-0 text-civic-700" />
                        <span>Marketplace</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- STAT COUNTER STRIP (Transparansi Data Layanan Terbuka Kota Madiun) --}}
    @if (($portalSettings['show_stats_section'] ?? '1') === '1')
        <section class="border-b border-civic-200/80 bg-white/95 py-6 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 lg:divide-x lg:divide-slate-200/70">
                    <div class="flex items-center gap-3.5 lg:px-6">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700 ring-1 ring-civic-200/60">
                            <flux:icon.building-office-2 class="size-5" />
                        </div>
                        <div>
                            <p class="font-display text-2xl font-extrabold text-slate-900 tabular-nums sm:text-3xl">{{ number_format($statistics['facilities'], 0, ',', '.') }}</p>
                            <p class="text-xs font-semibold text-slate-500">Fasilitas TPS3R Aktif</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3.5 lg:px-6">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700 ring-1 ring-civic-200/60">
                            <flux:icon.truck class="size-5" />
                        </div>
                        <div>
                            <p class="font-display text-2xl font-extrabold text-slate-900 tabular-nums sm:text-3xl">{{ number_format($statistics['routes'], 0, ',', '.') }}</p>
                            <p class="text-xs font-semibold text-slate-500">Rute Terjadwal</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3.5 lg:px-6">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700 ring-1 ring-civic-200/60">
                            <flux:icon.tag class="size-5" />
                        </div>
                        <div>
                            <p class="font-display text-2xl font-extrabold text-slate-900 tabular-nums sm:text-3xl">{{ number_format($statistics['products'], 0, ',', '.') }}</p>
                            <p class="text-xs font-semibold text-slate-500">Produk Daur Ulang</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3.5 lg:px-6">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700 ring-1 ring-civic-200/60">
                            <flux:icon.users class="size-5" />
                        </div>
                        <div>
                            <p class="font-display text-2xl font-extrabold text-slate-900 tabular-nums sm:text-3xl">{{ number_format($statistics['residents'], 0, ',', '.') }}</p>
                            <p class="text-xs font-semibold text-slate-500">Warga Terhubung</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- BANNER SLIDER DINAMIS: Pengumuman Resmi & Edukasi Lingkungan --}}
    @if (($portalSettings['show_banner_slider'] ?? '1') === '1')
        @php
            $sliderItems = collect();

            // 1. Masukkan pengumuman aktif
            foreach ($announcements->take(3) as $ann) {
                $sliderItems->push([
                    'type' => 'announcement',
                    'badge' => 'Pengumuman Resmi',
                    'badge_color' => 'bg-amber-100 text-amber-900 border-amber-200',
                    'icon_box' => 'bg-amber-50 border-amber-200 text-amber-800',
                    'title' => $ann->title,
                    'desc' => $ann->summary ?: Str::limit(strip_tags($ann->content), 120),
                    'date' => $ann->published_at?->translatedFormat('d F Y'),
                    'link' => route('public.services') . '#pengumuman',
                    'link_text' => 'Lihat Pengumuman',
                    'icon' => 'megaphone',
                ]);
            }

            // 2. Masukkan artikel edukasi unggulan
            foreach ($articles->take(3) as $art) {
                $sliderItems->push([
                    'type' => 'education',
                    'badge' => 'Edukasi & Tips',
                    'badge_color' => 'bg-emerald-100 text-emerald-900 border-emerald-200',
                    'icon_box' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                    'title' => $art->judul,
                    'desc' => $art->ringkasan ?: Str::limit(strip_tags($art->isi ?? ''), 120),
                    'date' => $art->published_at?->translatedFormat('d F Y'),
                    'link' => route('portal.articles.show', $art),
                    'link_text' => 'Baca Panduan',
                    'icon' => 'book-open',
                ]);
            }

            // Fallback jika belum ada data di database
            if ($sliderItems->isEmpty()) {
                $sliderItems->push([
                    'type' => 'info',
                    'badge' => 'Program Unggulan',
                    'badge_color' => 'bg-civic-100 text-civic-800 border-civic-200',
                    'icon_box' => 'bg-civic-50 border-civic-200 text-civic-800',
                    'title' => 'Gerakan Madiun Kota Bersih: Pilah dari Sumber Rumah Tangga',
                    'desc' => 'Dukung kelestarian lingkungan dengan memilah sampah organik dan anorganik. Dapatkan insentif poin yang bisa ditukar produk ramah lingkungan.',
                    'date' => now()->translatedFormat('d F Y'),
                    'link' => route('public.education'),
                    'link_text' => 'Pelajari Cara Pemilahan',
                    'icon' => 'sparkles',
                ]);
            }
        @endphp

        <section class="border-b border-slate-200/80 bg-slate-50/70 py-6" data-scroll-fade>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    x-data="{
                        active: 0,
                        total: {{ $sliderItems->count() }},
                        timer: null,
                        start() {
                            this.stop();
                            if (this.total <= 1 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                            this.timer = setInterval(() => {
                                this.active = (this.active + 1) % this.total;
                            }, 6000);
                        },
                        stop() {
                            if (this.timer) {
                                clearInterval(this.timer);
                                this.timer = null;
                            }
                        },
                        goTo(index) {
                            this.active = index;
                            this.stop();
                            this.start();
                        },
                        prev() {
                            this.active = (this.active - 1 + this.total) % this.total;
                            this.stop();
                            this.start();
                        },
                        next() {
                            this.active = (this.active + 1) % this.total;
                            this.stop();
                            this.start();
                        }
                    }"
                    x-init="start()"
                    @mouseenter="stop()"
                    @mouseleave="start()"
                    @focusin="stop()"
                    @focusout="start()"
                    data-test="home-content-slider"
                    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xs sm:p-6"
                >
                    <div data-test="home-content-slider-viewport" class="relative h-[190px] sm:h-[124px]">
                        @foreach ($sliderItems as $index => $item)
                            <div
                                x-cloak
                                x-show="active === {{ $index }}"
                                x-transition:enter="delay-150 transition-opacity duration-300 ease-out motion-reduce:transition-none"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition-opacity duration-150 ease-in motion-reduce:transition-none"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute inset-0 flex h-full w-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                style="{{ $index === 0 ? '' : 'display: none;' }}"
                            >
                                <div class="flex min-w-0 items-start gap-3.5 sm:max-w-2xl sm:items-center sm:gap-4 lg:max-w-3xl">
                                    <div class="hidden sm:flex size-11 shrink-0 items-center justify-center rounded-xl border {{ $item['icon_box'] }}">
                                        @if ($item['icon'] === 'megaphone')
                                            <flux:icon.megaphone class="size-5" />
                                        @elseif ($item['icon'] === 'book-open')
                                            <flux:icon.book-open class="size-5" />
                                        @else
                                            <flux:icon.sparkles class="size-5" />
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="inline-flex shrink-0 rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wide {{ $item['badge_color'] }}">
                                                {{ $item['badge'] }}
                                            </span>
                                            @if (!empty($item['date']))
                                                <span class="truncate text-[11px] font-medium text-slate-500">{{ $item['date'] }}</span>
                                            @endif
                                        </div>
                                        <h3 class="line-clamp-2 min-h-[2.5rem] font-display text-base font-bold leading-5 text-slate-900 sm:min-h-[2.75rem] sm:text-lg sm:leading-[1.375rem]">
                                            {{ $item['title'] }}
                                        </h3>
                                        <p class="line-clamp-2 min-h-10 text-xs leading-5 text-slate-600">
                                            {{ $item['desc'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-auto flex w-full shrink-0 items-center justify-end sm:mt-0 sm:w-auto sm:self-center">
                                    <a
                                        href="{{ $item['link'] }}"
                                        class="inline-flex max-w-full items-center gap-1.5 rounded-xl bg-civic-700 px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-civic-800"
                                    >
                                        <span class="truncate">{{ $item['link_text'] }}</span>
                                        <flux:icon.arrow-right class="size-3.5 shrink-0" />
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Navigation Controls & Dots --}}
                    @if ($sliderItems->count() > 1)
                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <div class="flex items-center gap-1.5">
                                @foreach ($sliderItems as $idx => $dummy)
                                    <button
                                        type="button"
                                        @click="goTo({{ $idx }})"
                                        class="size-2 rounded-full transition-all"
                                        :class="active === {{ $idx }} ? 'w-6 bg-civic-700' : 'bg-slate-200 hover:bg-slate-300'"
                                        aria-label="Slide {{ $idx + 1 }}"
                                    ></button>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="prev()"
                                    class="flex size-7 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                    aria-label="Slide Sebelumnya"
                                >
                                    <flux:icon.chevron-left class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    @click="next()"
                                    data-test="home-content-slider-next"
                                    class="flex size-7 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                    aria-label="Slide Selanjutnya"
                                >
                                    <flux:icon.chevron-right class="size-4" />
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- SECTION UNDUH APLIKASI WARGA & QR CODE DINAMIS (#unduh-aplikasi) --}}
    @if (($portalSettings['show_app_download_section'] ?? '1') === '1')
        @php
            $rawDownloadUrl = trim((string) ($portalSettings['app_download_url'] ?? ''));
            $hasDownloadTarget = $rawDownloadUrl !== '';
            $absoluteDownloadUrl = $hasDownloadTarget
                ? (\Illuminate\Support\Str::startsWith($rawDownloadUrl, ['http://', 'https://']) ? $rawDownloadUrl : url($rawDownloadUrl))
                : null;
            $hasQrCode = !empty($portalSettings['app_qr_custom_image']) || $hasDownloadTarget;
        @endphp
        <section id="unduh-aplikasi" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8" data-scroll-fade>
            <div class="overflow-hidden rounded-3xl border border-civic-200 bg-gradient-to-br from-white via-civic-50/50 to-emerald-50/40 p-6 sm:p-10 shadow-sm">
                <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-7">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-100 px-3 py-1 text-xs font-bold text-civic-800">
                            <flux:icon.sparkles class="size-3.5 text-civic-700" />
                            Aplikasi Warga Resmi Kota Madiun
                        </span>
                        <h2 class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                            {{ $portalSettings['app_download_title'] ?? 'Semua Urusan Sampah Lingkungan Selesai Lewat Ponsel' }}
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
                            {{ $portalSettings['app_download_subtitle'] ?? 'Aplikasi mobile Madiun Terpadu dirancang khusus untuk memudahkan warga memantau armada keliling, mengajukan jadwal penjemputan berkala, dan mencatat timbangan sampah langsung di depan rumah.' }}
                        </p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                    <flux:icon.map-pin class="size-5" />
                                </div>
                                <h4 class="mt-3 text-xs font-extrabold text-slate-900">Lacak Truk Real-time</h4>
                                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Lihat pergerakan armada keliling di kelurahan Anda.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                    <flux:icon.bell class="size-5" />
                                </div>
                                <h4 class="mt-3 text-xs font-extrabold text-slate-900">Pengingat Jadwal</h4>
                                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Notifikasi otomatis sebelum armada tiba di depan pagar.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                                <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                    <flux:icon.banknotes class="size-5" />
                                </div>
                                <h4 class="mt-3 text-xs font-extrabold text-slate-900">Poin Jadi Rupiah</h4>
                                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Hasil pilahan ditimbang resmi & dicatat ke akun warga.</p>
                            </div>
                        </div>

                        <div class="mt-7 flex flex-wrap items-center gap-3">
                            @if ($hasDownloadTarget)
                                <a href="{{ $absoluteDownloadUrl }}" download class="inline-flex min-h-12 items-center gap-2.5 rounded-xl bg-civic-700 px-6 text-sm font-bold text-white shadow-md shadow-civic-700/20 transition hover:bg-civic-800">
                                    <flux:icon.arrow-down-tray class="size-5" />
                                    <span>{{ $portalSettings['app_download_button_text'] ?? 'Unduh APK Android (Gratis)' }}</span>
                                </a>
                                <span class="text-xs text-slate-500">
                                    {{ $portalSettings['app_download_badge'] ?? 'Android 8.0+ · Gratis & Resmi DLH Kota Madiun' }}
                                </span>
                            @else
                                <span class="inline-flex min-h-12 items-center gap-2 rounded-xl border border-slate-200 bg-slate-100 px-5 text-sm font-bold text-slate-500">
                                    <flux:icon.clock class="size-5" />
                                    Unduhan belum tersedia
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- QR Code Scan Dinamis --}}
                    <div class="flex flex-col items-center justify-center lg:col-span-5">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm text-center">
                            <div class="mx-auto flex size-44 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50/40 p-3 shadow-inner">
                                @if (!empty($portalSettings['app_qr_custom_image']))
                                    <img src="{{ Storage::url($portalSettings['app_qr_custom_image']) }}" alt="QR Code Unduh Aplikasi" class="size-36 rounded-lg object-contain">
                                @elseif ($hasDownloadTarget)
                                    <div class="size-36 flex items-center justify-center [&_svg]:size-full [&_svg]:h-auto [&_svg]:w-full text-slate-900">
                                        {!! \App\Services\QrCodeService::generateSvg($absoluteDownloadUrl, 160) !!}
                                    </div>
                                @else
                                    <div class="flex size-36 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-slate-400">
                                        <flux:icon.qr-code class="size-12" />
                                    </div>
                                @endif
                            </div>
                            <p class="mt-3 text-xs font-bold text-slate-900">Pindai QR untuk Unduh di HP</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">Arahkan kamera smartphone Android Anda</p>
                            <span @class([
                                'mt-2 inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-semibold',
                                'border-emerald-200 bg-emerald-50 text-emerald-700' => $hasQrCode,
                                'border-slate-200 bg-slate-50 text-slate-500' => ! $hasQrCode,
                            ])>
                                <span @class([
                                    'size-1.5 rounded-full',
                                    'animate-pulse bg-emerald-500' => $hasQrCode,
                                    'bg-slate-400' => ! $hasQrCode,
                                ])></span>
                                {{ $hasQrCode ? 'QR Code Siap Dipindai' : 'Menunggu tautan resmi' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- DIREKTORI LAYANAN UTAMA --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" data-scroll-fade>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Direktori layanan</p>
                <h2 class="mt-1.5 font-display text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Pilih kebutuhan Anda</h2>
            </div>
            <p class="text-sm text-slate-500">Akses cepat modul layanan persampahan Kota Madiun.</p>
        </div>

        <div class="mt-6 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['title' => 'Jadwal & peta', 'text' => 'Cari rute pengangkutan dan titik TPS3R per kecamatan.', 'route' => 'public.services', 'meta' => $statistics['routes'].' rute aktif', 'icon' => 'map'],
                ['title' => 'Edukasi pilah', 'text' => 'Panduan pemilahan 3 kategori, artikel, dan dokumen resmi.', 'route' => 'public.education', 'meta' => 'Panduan & SOP', 'icon' => 'book'],
                ['title' => 'Marketplace', 'text' => 'Produk pupuk kompos dan kerajinan dari fasilitas TPS3R.', 'route' => 'public.marketplace', 'meta' => $statistics['products'].' produk siap beli', 'icon' => 'bag'],
                ['title' => 'Aduan kebersihan', 'text' => 'Laporkan sampah liar atau masalah pelayanan langsung ke DLH.', 'route' => 'login', 'meta' => 'Respon cepat', 'icon' => 'chat'],
            ] as $card)
                <a href="{{ route($card['route']) }}" class="civic-card-interactive group flex flex-col justify-between p-5">
                    <div>
                        <div class="flex size-11 items-center justify-center rounded-xl bg-civic-50 text-civic-700 transition-colors duration-200 group-hover:bg-civic-700 group-hover:text-white">
                            @if ($card['icon'] === 'map')
                                <flux:icon.map class="size-5" />
                            @elseif ($card['icon'] === 'book')
                                <flux:icon.book-open class="size-5" />
                            @elseif ($card['icon'] === 'bag')
                                <flux:icon.shopping-bag class="size-5" />
                            @else
                                <flux:icon.chat-bubble-bottom-center-text class="size-5" />
                            @endif
                        </div>
                        <h3 class="mt-4 font-display text-base font-extrabold text-slate-950">{{ $card['title'] }}</h3>
                        <p class="mt-1.5 text-xs leading-5 text-slate-600">{{ $card['text'] }}</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs font-semibold text-civic-700">
                        <span>{{ $card['meta'] }}</span>
                        <svg class="size-3.5 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7" /></svg>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- FASILITAS TPS3R KOTA MADIUN --}}
    <section class="border-y border-civic-200/80 bg-gradient-to-br from-civic-100/75 via-civic-50/75 to-white" data-scroll-fade>
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-4">
                    <span class="inline-flex rounded-md bg-civic-700 px-2.5 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.14em] text-white">Fasilitas Resmi</span>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950">TPS3R Kota Madiun</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Daftar titik penerimaan dan pengolahan sampah terpadu yang aktif beroperasi melayani warga di Kota Madiun.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-white px-3 py-1 font-semibold text-slate-700 shadow-2xs">3 Kecamatan</span>
                        <span class="rounded-full bg-white px-3 py-1 font-semibold text-slate-700 shadow-2xs">Kapasitas Harian Terdata</span>
                        <span class="rounded-full bg-white px-3 py-1 font-semibold text-slate-700 shadow-2xs">Pengolahan Kompos &amp; Maggot</span>
                    </div>
                    <a href="{{ route('public.services') }}" class="group mt-6 inline-flex min-h-10 items-center gap-2 rounded-xl bg-civic-700 px-4 text-sm font-bold text-white shadow-xs hover:bg-civic-800">
                        Buka peta &amp; jadwal lengkap
                        <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                    </a>
                </div>

                <div class="lg:col-span-8">
                    <div class="grid gap-4 sm:grid-cols-2">
                        @forelse ($facilities as $facility)
                            <a href="{{ route('public.facilities.show', $facility) }}" class="civic-card-interactive group block p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="size-2 rounded-full bg-emerald-500"></span>
                                            <span class="text-xs font-semibold text-civic-700">{{ $facility->kelurahan ? 'Kel. '.$facility->kelurahan : 'Unit TPS3R' }}</span>
                                        </div>
                                        <h3 class="mt-1 font-display text-base font-extrabold text-slate-950">{{ $facility->nama_tps3r }}</h3>
                                    </div>
                                    <span class="rounded-md bg-civic-100 px-2 py-0.5 text-[0.68rem] font-bold text-civic-800">Aktif</span>
                                </div>
                                <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-600">{{ $facility->alamat }}</p>
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                                    <span class="font-semibold text-slate-500">
                                        {{ $facility->kecamatan ? 'Kec. '.$facility->kecamatan : 'Kota Madiun' }}{{ $facility->kapasitas_harian_kg ? ' · '.number_format($facility->kapasitas_harian_kg, 0, ',', '.').' kg/hari' : '' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-bold text-civic-700">
                                        Lihat profil
                                        <svg class="size-3.5 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7" /></svg>
                                    </span>
                                </div>
                            </a>
                        @empty
                            <x-public.empty-state class="sm:col-span-2" title="Fasilitas belum dipublikasikan" description="Data fasilitas akan tampil setelah profil TPS3R diaktifkan." />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- EDUKASI TERBARU --}}
    @if (($portalSettings['show_education_section'] ?? '1') === '1')
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8" data-scroll-fade>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Edukasi terbaru</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950">Panduan praktis warga</h2>
                </div>
                <a href="{{ route('public.education') }}" class="group inline-flex min-h-10 items-center gap-2 rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 hover:border-civic-400 hover:bg-civic-50">
                    Lihat semua edukasi
                    <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                </a>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($articles as $article)
                    @php
                        $articleCover = $article->cover_url ?: $article->thumbnail_url;
                    @endphp
                    <article class="civic-card-interactive group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-civic-300 hover:shadow-md">
                        @if ($articleCover)
                            <div class="aspect-video w-full overflow-hidden bg-slate-100">
                                <img src="{{ $articleCover }}" alt="{{ $article->judul }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                            </div>
                        @else
                            <div class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-civic-50 to-civic-100/70 text-civic-400">
                                <flux:icon.book-open class="size-10 text-civic-500/80" />
                            </div>
                        @endif
                        <div class="flex flex-1 flex-col justify-between p-5">
                            <div>
                                <span class="rounded-md bg-civic-50 px-2.5 py-1 text-[0.68rem] font-bold text-civic-700 ring-1 ring-civic-200">{{ $article->kategori_label ?? ($article->kategori ? ucfirst($article->kategori) : 'Panduan') }}</span>
                                <h3 class="mt-3 line-clamp-2 min-h-[3rem] font-display text-base font-extrabold text-slate-950 group-hover:text-civic-700">
                                    <a href="{{ route('portal.articles.show', $article) }}" class="focus:outline-none">
                                        {{ $article->judul }}
                                    </a>
                                </h3>
                                <p class="mt-2 line-clamp-2 min-h-[2.5rem] text-xs leading-5 text-slate-600">
                                    {{ $article->ringkasan ?: 'Pelajari panduan dan langkah praktis pemilahan serta pengelolaan sampah terpadu Kota Madiun.' }}
                                </p>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                                <span>{{ $article->published_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</span>
                                <span class="font-bold text-civic-700 group-hover:underline">Baca selengkapnya</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-public.empty-state class="sm:col-span-2 lg:col-span-3" title="Belum ada artikel edukasi" description="Artikel dan tips pemilahan sampah akan segera dipublikasikan." />
                @endforelse
            </div>
        </section>
    @endif

    {{-- MARKETPLACE PRODUK OLAHAN --}}
    @if (($portalSettings['show_marketplace_section'] ?? '1') === '1')
        <section class="border-t border-slate-200/80 bg-slate-50/70" data-scroll-fade>
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Ekonomi sirkular</p>
                        <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950">Produk olahan TPS3R</h2>
                    </div>
                    <a href="{{ route('public.marketplace') }}" class="group inline-flex min-h-10 items-center gap-2 rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 hover:border-civic-400 hover:bg-civic-50">
                        Buka katalog lengkap
                        <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                    </a>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse (($products ?? $featuredProducts ?? $featured_products ?? []) as $product)
                        <x-public.product-card :product="$product" />
                    @empty
                        <x-public.empty-state class="sm:col-span-2 lg:col-span-4" title="Belum ada produk daur ulang" description="Katalog pupuk kompos dan kerajinan daur ulang akan segera tersedia." />
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    <x-public.action-band />
@endsection
