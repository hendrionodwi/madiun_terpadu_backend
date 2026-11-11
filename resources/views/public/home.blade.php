@extends('layouts.public', ['title' => 'Portal Publik'])

@section('content')
    <section class="border-b border-civic-200/80 bg-gradient-to-br from-civic-100/60 via-civic-50/40 to-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-16 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:px-8">
            <div class="portal-settle">
                <span class="inline-flex items-center gap-2 rounded-full border border-civic-200 bg-white px-3.5 py-1 text-xs font-bold text-civic-800 shadow-2xs">
                    <span class="size-2 rounded-full bg-civic-600"></span>
                    {{ $portalSettings['hero_kicker'] }}
                </span>

                <h1 class="mt-5 max-w-2xl font-display text-3xl font-extrabold leading-[1.14] tracking-[-0.03em] text-slate-950 sm:text-4xl lg:text-[2.75rem]">
                    {{ $portalSettings['hero_title'] }}
                </h1>

                <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">
                    {{ $portalSettings['hero_subtitle'] }}
                </p>

                <form method="GET" action="{{ route('public.services') }}" class="mt-7 flex max-w-xl flex-col gap-2 rounded-2xl border border-civic-200 bg-white p-2 shadow-sm transition focus-within:border-civic-500 focus-within:ring-4 focus-within:ring-civic-100 sm:flex-row sm:items-center">
                    <label class="relative flex flex-1 items-center">
                        <svg class="pointer-events-none ml-3 size-4.5 shrink-0 text-civic-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <input
                            type="text"
                            name="search"
                            placeholder="Cari nama TPS3R, kelurahan, atau rute penjemputan..."
                            class="min-h-11 w-full border-0 bg-transparent px-3 text-sm text-slate-900 outline-none placeholder:text-slate-400"
                        >
                    </label>
                    <button type="submit" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-civic-700 px-5 text-sm font-bold text-white hover:bg-civic-800">
                        Cari layanan
                    </button>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                    <span class="font-semibold text-slate-500">Wilayah:</span>
                    @foreach (['Kartoharjo', 'Manguharjo', 'Taman'] as $districtShortcut)
                        <a href="{{ route('public.services', ['district' => $districtShortcut]) }}" class="rounded-full border border-civic-200 bg-white px-3 py-1 font-semibold text-civic-800 transition hover:border-civic-400 hover:bg-civic-50">
                            Kec. {{ $districtShortcut }}
                        </a>
                    @endforeach
                </div>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ route('public.services') }}" class="group inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#172536] px-5 text-sm font-bold text-white shadow-xs hover:bg-civic-800">
                        Lihat jadwal &amp; peta
                        <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                    </a>
                    <a href="{{ route('public.marketplace') }}" class="inline-flex min-h-11 items-center rounded-xl border border-civic-200 bg-white px-5 text-sm font-bold text-civic-800 hover:border-civic-400 hover:bg-civic-50">
                        Buka marketplace
                    </a>
                </div>
            </div>

            <div class="portal-settle-delay-1">
                @if (($portalSettings['show_stats_section'] ?? '1') === '1')
                    <div class="overflow-hidden rounded-2xl border border-civic-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-civic-800/15 bg-[#172536] px-5 py-3.5 text-white">
                            <div>
                                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.14em] text-civic-300">Ringkasan Operasional</p>
                                <p class="text-sm font-bold">Data Layanan Terbuka Kota Madiun</p>
                            </div>
                            <span class="rounded-full bg-civic-700 px-2.5 py-1 text-[0.7rem] font-bold text-white">Terverifikasi</span>
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-y divide-slate-100">
                            @foreach ([
                                ['label' => 'Fasilitas aktif', 'value' => $statistics['facilities'], 'suffix' => 'unit TPS3R'],
                                ['label' => 'Rute terjadwal', 'value' => $statistics['routes'], 'suffix' => 'rute armada'],
                                ['label' => 'Produk tersedia', 'value' => $statistics['products'], 'suffix' => 'item daur ulang'],
                                ['label' => 'Warga terhubung', 'value' => $statistics['residents'], 'suffix' => 'akun terdaftar'],
                            ] as $stat)
                                <div class="p-5 transition-colors duration-200 hover:bg-civic-50/50">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                                    <p class="mt-2 font-display text-3xl font-extrabold text-civic-800 tabular-nums">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $stat['suffix'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        @if (! empty($portalSettings['transparency_note']))
                            <div class="border-t border-slate-100 bg-civic-50/70 px-5 py-3 text-xs leading-5 text-slate-600">
                                {{ $portalSettings['transparency_note'] }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-2xl border border-civic-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-civic-700">Status Layanan</p>
                        <p class="mt-2 font-display text-xl font-extrabold text-slate-950">{{ $portalSettings['operational_status_label'] ?? 'Pelayanan Wilayah Normal' }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $portalSettings['hero_badge_text'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

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
                ['title' => 'Pengaduan', 'text' => 'Laporkan timbunan liar atau jadwal terlambat setelah masuk.', 'route' => 'login', 'meta' => 'Akun warga', 'icon' => 'alert'],
            ] as $service)
                <a href="{{ route($service['route']) }}" class="portal-interactive-card group flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5">
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex size-10 items-center justify-center rounded-lg bg-civic-50 text-civic-700 transition-colors duration-200 group-hover:bg-civic-700 group-hover:text-white">
                                @if ($service['icon'] === 'map')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z" /><path d="M9 3v15M15 6v15" /></svg>
                                @elseif ($service['icon'] === 'book')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M4 4v15.5A2.5 2.5 0 0 0 6.5 22H20V4H6.5A2.5 2.5 0 0 0 4 6.5" /></svg>
                                @elseif ($service['icon'] === 'bag')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 8h14l-1 13H6L5 8Z" /><path d="M9 9V6a3 3 0 0 1 6 0v3" /></svg>
                                @else
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" /><path d="M12 7v6M12 17h.01" /></svg>
                                @endif
                            </span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[0.7rem] font-semibold text-slate-600 tabular-nums">{{ $service['meta'] }}</span>
                        </div>
                        <h3 class="mt-4 font-display text-base font-extrabold text-slate-950 group-hover:text-civic-800">{{ $service['title'] }}</h3>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600">{{ $service['text'] }}</p>
                    </div>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-civic-700">
                        Buka layanan
                        <svg class="size-3.5 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7" /></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="border-y border-civic-200/80 bg-gradient-to-br from-civic-100/75 via-civic-50/75 to-white" data-scroll-fade>
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-4">
                    <span class="inline-flex rounded-md bg-civic-700 px-2.5 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.14em] text-white">Fasilitas Resmi</span>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950">TPS3R Kota Madiun</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Daftar titik penerimaan dan pengolahan sampah terpadu yang aktif beroperasi melayani warga di Kota Madiun.
                    </p>
                    <div class="mt-5 inline-flex items-center gap-2 rounded-lg border border-civic-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 tabular-nums">
                        <span class="size-2 rounded-full bg-civic-600"></span>
                        <span>Menampilkan {{ $facilities->count() }} dari {{ $statistics['facilities'] }} fasilitas aktif</span>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('public.services') }}" class="group inline-flex min-h-11 items-center gap-2 rounded-xl bg-civic-700 px-5 text-sm font-bold text-white shadow-xs hover:bg-civic-800">
                            Lihat semua fasilitas &amp; peta
                            <svg class="size-4 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>

                @php
                    $facilityGridClass = $facilities->count() <= 1 ? 'grid gap-4' : 'grid gap-4 sm:grid-cols-2';
                @endphp
                <div class="lg:col-span-8">
                    <div class="{{ $facilityGridClass }}">
                        @forelse ($facilities as $facility)
                            <a href="{{ route('public.services', array_filter(['district' => $facility->kecamatan, 'search' => $facility->nama_tps3r])) }}" class="portal-interactive-card group flex flex-col justify-between rounded-2xl border border-civic-200/90 bg-white p-5">
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="rounded-md bg-slate-100 px-2.5 py-1 font-mono text-[0.7rem] font-bold text-slate-700">
                                            {{ $facility->kode_tps3r ?: 'TPS3R' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-50 px-2.5 py-1 text-xs font-bold text-civic-800 ring-1 ring-civic-200/80">
                                            <span class="size-1.5 rounded-full bg-civic-600"></span>
                                            Aktif
                                        </span>
                                    </div>
                                    <h3 class="mt-4 font-display text-lg font-extrabold text-slate-950 group-hover:text-civic-800">{{ $facility->nama_tps3r }}</h3>
                                    <p class="mt-1.5 text-sm leading-6 text-slate-600">{{ $facility->alamat ?: 'Alamat fasilitas belum dilengkapi.' }}</p>

                                    @if ($facility->jam_operasional || $facility->fokus_pengolahan)
                                        <div class="mt-3 space-y-1 rounded-xl bg-slate-50 px-3 py-2.5 text-xs text-slate-600">
                                            @if ($facility->jam_operasional)
                                                <p>Jam layanan: <span class="font-semibold text-slate-800">{{ $facility->jam_operasional }}</span></p>
                                            @endif
                                            @if ($facility->fokus_pengolahan)
                                                <p>Fokus olahan: <span class="font-semibold text-civic-800">{{ $facility->fokus_pengolahan }}</span></p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs">
                                    <span class="font-semibold text-slate-600 tabular-nums">
                                        {{ $facility->kecamatan ? 'Kec. '.$facility->kecamatan : 'Kota Madiun' }}{{ $facility->kapasitas_harian_kg ? ' · '.number_format($facility->kapasitas_harian_kg, 0, ',', '.').' kg/hari' : '' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-bold text-civic-700">
                                        Lihat di peta
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

    @if (($portalSettings['show_education_section'] ?? '1') === '1')
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8" data-scroll-fade>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Edukasi terbaru</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950">Panduan praktis warga</h2>
                </div>
                <a href="{{ route('public.education') }}" class="group inline-flex min-h-10 items-center gap-2 rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 hover:border-civic-400 hover:bg-civic-50">
                    Semua edukasi &amp; dokumen
                    <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                </a>
            </div>

            @php
                $articleGridClass = match (true) {
                    $articles->count() <= 1 => 'mt-8 grid gap-5',
                    $articles->count() === 2 => 'mt-8 grid gap-5 md:grid-cols-2',
                    default => 'mt-8 grid gap-5 md:grid-cols-3',
                };
            @endphp
            <div class="{{ $articleGridClass }}">
                @forelse ($articles as $article)
                    <a href="{{ route('portal.articles.show', $article) }}" class="portal-interactive-card group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div>
                            @if ($article->cover_url)
                                <div class="overflow-hidden bg-slate-100">
                                    <img src="{{ $article->cover_url }}" alt="{{ $article->judul }}" class="h-44 w-full object-cover transition duration-500 ease-out group-hover:scale-[1.03]">
                                </div>
                            @endif
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <span class="rounded-full bg-civic-50 px-2.5 py-1 font-bold text-civic-800 ring-1 ring-civic-200/70">{{ $article->kategori_label }}</span>
                                    <span class="text-slate-500 tabular-nums">{{ $article->waktu_baca_menit }} menit baca</span>
                                </div>
                                <h3 class="mt-4 font-display text-lg font-extrabold leading-6 text-slate-950 group-hover:text-civic-800">{{ $article->judul }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{{ $article->ringkasan }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-xs font-semibold text-slate-500">
                            <span>{{ $article->published_at?->translatedFormat('d M Y') }}</span>
                            <span class="inline-flex items-center gap-1 font-bold text-civic-700">
                                Baca panduan
                                <svg class="size-3.5 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7" /></svg>
                            </span>
                        </div>
                    </a>
                @empty
                    <x-public.empty-state class="md:col-span-3" title="Artikel belum dipublikasikan" description="Artikel edukasi dari Admin DLH akan tampil otomatis." />
                @endforelse
            </div>
        </section>
    @endif

    @if (($portalSettings['show_marketplace_section'] ?? '1') === '1')
        <section class="border-t border-slate-200/80 bg-slate-50/70" data-scroll-fade>
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Ekonomi sirkular</p>
                        <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950">Produk terbaru</h2>
                    </div>
                    <a href="{{ route('public.marketplace') }}" class="group inline-flex min-h-10 items-center gap-2 rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 hover:border-civic-400 hover:bg-civic-50">
                        Buka katalog lengkap
                        <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                    </a>
                </div>

                @php
                    $productGridClass = match (true) {
                        $featuredProducts->count() === 1 => 'mt-8 grid gap-5 sm:max-w-sm',
                        $featuredProducts->count() === 2 => 'mt-8 grid gap-5 sm:grid-cols-2',
                        default => 'mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3',
                    };
                @endphp
                <div class="{{ $productGridClass }}">
                    @forelse ($featuredProducts as $product)
                        <x-public.product-card :product="$product" />
                    @empty
                        <x-public.empty-state class="sm:col-span-2 lg:col-span-3" title="Belum ada produk tersedia" description="Produk aktif dari TPS3R akan tampil di sini." />
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    @if ($announcements->isNotEmpty() && ($portalSettings['announcement_banner_enabled'] ?? '1') === '1')
        <section class="mx-auto max-w-7xl px-4 pb-2 pt-8 sm:px-6 lg:px-8" data-scroll-fade>
            <div class="rounded-2xl border border-civic-200 bg-civic-50/70 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-civic-200/70 pb-3">
                    <div>
                        <p class="text-[0.7rem] font-extrabold uppercase tracking-[0.14em] text-civic-700">Informasi Terkini</p>
                        <h2 class="mt-0.5 font-display text-lg font-extrabold text-slate-950">Pengumuman Resmi DLH</h2>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-civic-800 ring-1 ring-civic-200 tabular-nums">{{ $announcements->count() }} pemberitahuan</span>
                </div>
                <div class="divide-y divide-civic-200/60">
                    @foreach ($announcements->take(3) as $notice)
                        <div class="flex flex-col gap-1.5 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">{{ $notice->title }}</p>
                                @if ($notice->summary)
                                    <p class="mt-0.5 line-clamp-1 text-sm text-slate-600">{{ $notice->summary }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-full bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-200 tabular-nums">{{ $notice->published_at?->translatedFormat('d M Y') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-public.action-band />
@endsection
