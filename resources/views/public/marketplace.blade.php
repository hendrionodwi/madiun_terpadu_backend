@extends('layouts.public', ['title' => 'Marketplace Daur Ulang'])

@section('content')
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 lg:px-8 portal-settle">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <nav class="flex items-center gap-2 text-slate-500" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="font-medium hover:text-civic-700">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-bold text-slate-900">Marketplace daur ulang</span>
                </nav>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-50 px-3 py-1 font-bold text-civic-800 ring-1 ring-civic-200/80">
                    <span class="size-1.5 rounded-full bg-civic-600"></span>
                    Katalog Publik Terbuka
                </span>
            </div>

            <div class="mt-4">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Ekonomi sirkular</p>
                <h1 class="mt-1.5 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Marketplace Produk Daur Ulang</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                    Temukan produk hasil pengolahan sampah dan karya ekonomi sirkular langsung dari pengelola TPS3R dan mitra lingkungan Kota Madiun.
                </p>
            </div>

            <div class="mt-5 flex flex-col justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 sm:flex-row sm:items-center">
                <p>
                    <span class="font-bold text-slate-800">Akses Publik:</span> Pengunjung dapat melihat produk dan membandingkan toko TPS3R tanpa login. Masuk akun Warga diperlukan untuk keranjang dan checkout.
                </p>
                @auth
                    <a href="{{ route('dashboard') }}" class="shrink-0 font-bold text-civic-700 hover:text-civic-900">Buka Dashboard →</a>
                @else
                    <a href="{{ route('login') }}" class="shrink-0 font-bold text-civic-700 hover:text-civic-900">Masuk untuk membeli →</a>
                @endauth
            </div>

            <div class="mt-6 grid gap-6 overflow-hidden rounded-2xl bg-gradient-to-br from-civic-800 via-civic-900 to-[#0d3b33] p-6 text-white shadow-sm lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:p-8">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-civic-100">
                        Inisiatif Sirkular Kota Madiun
                    </span>
                    <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">Dukung Ekonomi Sirkular Kota Madiun</h2>
                    <p class="mt-2.5 max-w-xl text-sm leading-6 text-civic-100/85">
                        Setiap pembelian produk daur ulang langsung mendukung keberlanjutan operasional fasilitas TPS3R dan kelompok pemilah swadaya di kelurahan se-Kota Madiun.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('public.education') }}" class="inline-flex min-h-10 items-center rounded-xl bg-white px-4 text-xs font-extrabold text-civic-900 hover:bg-civic-50 sm:text-sm">
                            Pelajari Proses Pengolahan
                        </a>
                        <a href="{{ route('public.services') }}" class="inline-flex min-h-10 items-center rounded-xl border border-white/25 bg-white/10 px-4 text-xs font-bold text-white hover:bg-white/15 sm:text-sm">
                            Temukan TPS3R
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-5 text-civic-ink shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 text-xs">
                        <span class="font-extrabold uppercase tracking-wider text-slate-500">Fokus Pengolahan</span>
                        <span class="font-bold text-civic-700">● Terintegrasi DLH</span>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-civic-100 text-civic-700">
                                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path d="M8 13c4 0 6-2 7-6 2 5 0 10-5 11" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-extrabold text-slate-900">Kompos Organik &amp; POC</p>
                                <p class="text-xs text-slate-500">Dikelola {{ $stores->count() }} fasilitas TPS3R aktif</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-civic-100 text-civic-700">
                                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z" /><path d="M9 9V6a3 3 0 0 1 6 0v3" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-extrabold text-slate-900">Karya Daur Ulang Resik</p>
                                <p class="text-xs text-slate-500">Kerajinan &amp; bahan pilah kualitas teruji</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                        <span class="text-slate-500">Pembayaran Resmi</span>
                        <span class="font-bold text-slate-800">Rupiah · Poin Dipilih Terpisah saat Checkout</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section data-ajax-zone="marketplace" data-ajax-targets="#marketplaceCategoryPills,#marketplaceResultSummary,#marketplaceResults" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @php
            $sortOptions = [
                'terbaru' => 'Urutkan: Terbaru',
                'termurah' => 'Harga Termurah',
                'termahal' => 'Harga Termahal',
            ];
            $storeOptions = collect(['' => 'Semua Penjual / Fasilitas'])
                ->merge($stores->mapWithKeys(fn ($store) => [$store->public_id => $store->nama_tps3r]))
                ->all();
            $priceOptions = [
                '' => 'Semua Rentang Harga',
                'under_25k' => 'Di bawah Rp25.000',
                '25k_50k' => 'Rp25.000 – Rp50.000',
                'above_50k' => 'Di atas Rp50.000',
            ];
        @endphp

        <form id="marketplaceFilterForm" method="GET" action="{{ route('public.marketplace') }}" data-ajax-form data-ajax-targets="#marketplaceCategoryPills,#marketplaceResultSummary,#marketplaceResults" class="relative z-20 rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <div class="grid gap-3 md:grid-cols-[1fr_13.5rem_auto]">
                <label class="relative block">
                    <span class="sr-only">Cari produk</span>
                    <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                    <input
                        id="marketplaceSearchInput"
                        name="search"
                        value="{{ $filters['search'] }}"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-civic-500 focus:bg-white focus:ring-4 focus:ring-civic-100"
                        placeholder="Cari kompos, media tanam, kerajinan, atau produk olahan..."
                    >
                </label>

                <x-public.select-filter
                    name="sort"
                    :value="$filters['sort']"
                    :options="$sortOptions"
                    placeholder="Urutkan"
                    height-class="h-11"
                    :align-right="true"
                />

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center rounded-xl bg-civic-700 px-5 text-sm font-bold text-white hover:bg-civic-800 md:flex-none">Cari</button>
                    <a href="{{ route('public.marketplace') }}" data-ajax-link class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 hover:border-slate-300 hover:bg-slate-50">Reset</a>
                </div>
            </div>

            <div id="marketplaceCategoryPills" class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                <span class="mr-1 text-xs font-semibold text-slate-500">Kategori:</span>
                <a
                    href="{{ route('public.marketplace', array_filter(['search' => $filters['search'], 'store' => $filters['store'], 'price_range' => $filters['price_range'], 'sort' => $filters['sort'] !== 'terbaru' ? $filters['sort'] : null])) }}"
                    data-ajax-link
                    @class([
                        'rounded-full px-3.5 py-1.5 text-xs font-bold transition',
                        'bg-civic-700 text-white shadow-2xs' => $filters['category'] === '',
                        'border border-slate-200 bg-slate-50 text-slate-700 hover:border-civic-300 hover:bg-civic-50 hover:text-civic-800' => $filters['category'] !== '',
                    ])
                >
                    Semua ({{ number_format($totalAvailableProducts, 0, ',', '.') }})
                </a>
                @foreach ($categories as $category)
                    <a
                        href="{{ route('public.marketplace', array_filter(['category' => $category, 'search' => $filters['search'], 'store' => $filters['store'], 'price_range' => $filters['price_range'], 'sort' => $filters['sort'] !== 'terbaru' ? $filters['sort'] : null])) }}"
                        data-ajax-link
                        @class([
                            'rounded-full px-3.5 py-1.5 text-xs font-bold transition',
                            'bg-civic-700 text-white shadow-2xs' => $filters['category'] === $category,
                            'border border-slate-200 bg-slate-50 text-slate-700 hover:border-civic-300 hover:bg-civic-50 hover:text-civic-800' => $filters['category'] !== $category,
                        ])
                    >
                        {{ str($category)->replace('_', ' ')->title() }} ({{ number_format((int) ($categoryCounts[$category] ?? 0), 0, ',', '.') }})
                    </a>
                @endforeach
            </div>

            <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                <div class="grid flex-1 gap-3 sm:grid-cols-2 lg:max-w-xl">
                    <x-public.select-filter
                        name="store"
                        :value="$filters['store']"
                        :options="$storeOptions"
                        placeholder="Semua Penjual / Fasilitas"
                        height-class="h-10.5"
                    />

                    <x-public.select-filter
                        name="price_range"
                        :value="$filters['price_range']"
                        :options="$priceOptions"
                        placeholder="Semua Rentang Harga"
                        height-class="h-10.5"
                    />
                </div>

                <input id="marketplaceCategoryInput" type="hidden" name="category" value="{{ $filters['category'] }}">

                <p id="marketplaceResultSummary" class="text-xs text-slate-500">
                    Menampilkan <strong class="font-bold text-slate-900 tabular-nums">{{ $products->total() }}</strong> produk tersedia
                </p>
            </div>
        </form>

        <div id="marketplaceResults" class="transition-opacity duration-200">

        @if ($featuredProduct)
            <div class="mt-10" data-scroll-fade>
                <div class="flex items-center justify-between gap-4">
                    <h2 class="flex items-center gap-2 font-display text-lg font-extrabold text-slate-950">
                        <svg class="size-5 text-civic-700" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" /></svg>
                        Produk Unggulan TPS3R
                    </h2>
                    <span class="text-xs font-semibold text-slate-500">Pilihan Terpopuler Warga Kota</span>
                </div>

                <div class="portal-interactive-card mt-4 grid overflow-hidden rounded-2xl border border-civic-200 bg-white lg:grid-cols-[1.05fr_1fr]">
                    <div class="relative aspect-[16/10] bg-civic-50 lg:aspect-auto lg:min-h-[18rem]">
                        @if ($featuredProduct->foto_url)
                            <img src="{{ $featuredProduct->foto_url }}" alt="{{ $featuredProduct->nama_produk }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full min-h-[15rem] items-center justify-center text-civic-600">
                                <svg class="size-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path d="M8 13c4 0 6-2 7-6 2 5 0 10-5 11" /></svg>
                            </div>
                        @endif
                        <span class="absolute top-4 left-4 rounded-md bg-[#172536] px-2.5 py-1 text-[0.7rem] font-extrabold uppercase tracking-wider text-white">Produk Unggulan TPS3R</span>
                    </div>

                    <div class="flex flex-col justify-between p-6 sm:p-8">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-full bg-civic-100 px-2.5 py-0.5 font-bold text-civic-800">{{ str($featuredProduct->kategori)->replace('_', ' ')->title() }}</span>
                                <span class="text-slate-400">·</span>
                                <span class="font-semibold text-slate-600">
                                    {{ $featuredProduct->seller->pengelolaTps3rProfile?->nama_tps3r ?? $featuredProduct->seller->name }}
                                    @if ($featuredProduct->seller->pengelolaTps3rProfile?->kecamatan)
                                        (Kec. {{ $featuredProduct->seller->pengelolaTps3rProfile->kecamatan }})
                                    @endif
                                </span>
                            </div>

                            <h3 class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-950">{{ $featuredProduct->nama_produk }}</h3>
                            <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">
                                {{ $featuredProduct->deskripsi ?: 'Produk olahan ramah lingkungan berkualitas siap pakai dari fasilitas TPS3R Kota Madiun.' }}
                            </p>

                            <div class="mt-5 grid grid-cols-3 gap-4 border-y border-slate-100 py-4 text-xs tabular-nums">
                                <div>
                                    <p class="text-slate-500">Harga Satuan</p>
                                    <p class="mt-1 font-display text-xl font-extrabold text-civic-800">Rp{{ number_format($featuredProduct->harga, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Ketersediaan Fisik</p>
                                    <p class="mt-1 text-base font-extrabold text-slate-900">{{ number_format($featuredProduct->stok, 0, ',', '.') }} tersedia</p>
                                    <p class="font-semibold text-civic-700">Stok di Gudang TPS3R</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Pengambilan</p>
                                    <p class="mt-1 font-bold text-slate-900">Mandiri / Kurir TPS3R</p>
                                    <p class="text-slate-500">{{ $featuredProduct->seller->pengelolaTps3rProfile?->kelurahan ?? 'Kota Madiun' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            @if ($featuredProduct->seller->pengelolaTps3rProfile)
                                <a href="{{ route('public.marketplace', ['store' => $featuredProduct->seller->pengelolaTps3rProfile->public_id]) }}" class="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 hover:border-civic-300 hover:text-civic-800">
                                    Lihat Produk Toko
                                </a>
                            @endif
                            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-civic-700 px-5 text-sm font-bold text-white hover:bg-civic-800">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z" /><path d="M9 9V6a3 3 0 0 1 6 0v3" /></svg>
                                {{ auth()->check() ? 'Beli di Dashboard' : 'Masuk untuk Membeli' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-12 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-2xl font-extrabold tracking-tight text-slate-950">Katalog Hasil Daur Ulang Warga</h2>
                <p class="mt-1 text-sm text-slate-500">Produk resmi binaan pengolahan sampah mandiri kelurahan Kota Madiun</p>
            </div>
            @if (collect($filters)->filter(fn ($v, $k) => $k !== 'sort' ? filled($v) : $v !== 'terbaru')->isNotEmpty())
                <a href="{{ route('public.marketplace') }}" data-marketplace-reset class="text-sm font-bold text-civic-700 hover:text-civic-900">Reset semua filter</a>
            @endif
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-scroll-fade>
            @forelse ($products as $product)
                <x-public.product-card :product="$product" />
            @empty
                <x-public.empty-state class="sm:col-span-2 lg:col-span-3 xl:col-span-4" title="Produk tidak ditemukan" description="Coba ubah kata kunci pencarian atau pilih kategori lain." />
            @endforelse
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
        </div>
    </section>

    <section class="border-y border-civic-200/70 bg-civic-50/70 py-14" data-scroll-fade>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <span class="inline-flex rounded-md bg-civic-100 px-2.5 py-1 text-[0.7rem] font-extrabold uppercase tracking-wider text-civic-800">
                Transparansi Fasilitas Mitra
            </span>
            <h2 class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Dikelola oleh TPS3R dan Mitra Lingkungan</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Seluruh produk dihasilkan langsung oleh pengelola fasilitas pengolahan sampah berbasis masyarakat yang terdaftar resmi pada Dinas Lingkungan Hidup Kota Madiun.
            </p>

            @php
                $storeGridClass = match (true) {
                    $stores->count() <= 1 => 'mt-8 grid gap-4',
                    $stores->count() === 2 => 'mt-8 grid gap-4 md:grid-cols-2',
                    default => 'mt-8 grid gap-4 md:grid-cols-3',
                };
            @endphp
            <div class="{{ $storeGridClass }}">
                @forelse ($stores as $store)
                    @php
                        $activeCount = (int) ($store->user?->active_products_count ?? 0);
                    @endphp
                    <div data-marketplace-store-card class="portal-interactive-card flex flex-col justify-between rounded-2xl border border-civic-200/90 bg-white p-5">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex size-9 items-center justify-center rounded-lg bg-civic-50 text-civic-700">
                                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 21h18M5 21V9l7-4 7 4v12M9 21v-6h6v6" /></svg>
                                </span>
                                <span class="rounded-full bg-civic-50 px-2.5 py-0.5 text-[0.7rem] font-bold text-civic-800 ring-1 ring-civic-200/80">
                                    ● Aktif Berproduksi
                                </span>
                            </div>
                            <h3 class="mt-4 font-display text-lg font-extrabold text-slate-950">{{ $store->nama_tps3r }}</h3>
                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ $store->kelurahan ? 'Kel. '.$store->kelurahan.', ' : '' }}{{ $store->kecamatan ? 'Kec. '.$store->kecamatan.', ' : '' }}Kota Madiun
                            </p>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $store->fokus_pengolahan ?: ($store->alamat ?: 'Pusat pengolahan sampah terpadu & pemberdayaan ekonomi sirkular warga.') }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs font-bold">
                            <a href="{{ route('public.marketplace', ['store' => $store->public_id]) }}" data-ajax-link data-ajax-targets="#marketplaceCategoryPills,#marketplaceResultSummary,#marketplaceResults" data-ajax-scroll class="text-civic-700 hover:text-civic-900">
                                Lihat {{ $activeCount > 0 ? $activeCount.' Produk' : 'Toko' }} →
                            </a>
                            <a href="{{ route('public.services', array_filter(['district' => $store->kecamatan, 'search' => $store->nama_tps3r])) }}" class="text-slate-600 hover:text-civic-800">
                                Peta Lokasi
                            </a>
                        </div>
                    </div>
                @empty
                    <x-public.empty-state class="md:col-span-3" title="Belum ada fasilitas mitra" description="Profil toko TPS3R aktif akan ditampilkan di sini." />
                @endforelse
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" data-scroll-fade>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-civic-700 text-white">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" /></svg>
                </span>
                <div>
                    <h2 class="font-display text-lg font-extrabold text-slate-950">Informasi Tata Kelola Marketplace</h2>
                    <p class="mt-1 text-xs text-slate-500">Prinsip akuntabilitas dan prosedur layanan ekonomi sirkular resmi DLH Kota Madiun.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Harga Transaksi Resmi', 'copy' => 'Seluruh harga ditampilkan dalam mata uang Rupiah murni. Pendapatan disalurkan ke kas pengelola TPS3R terkait.'],
                    ['title' => 'Poin Warga Terpisah', 'copy' => 'Satuan poin apresiasi pemilahan warga hanya dipakai saat checkout akun terdaftar dan tidak dicampuradukkan dengan harga barang.'],
                    ['title' => 'Metode Penerimaan', 'copy' => 'Pesanan dapat diantar kurir mitra TPS3R setempat atau diambil mandiri oleh pemesan di fasilitas pengolah.'],
                    ['title' => 'Keranjang Satu TPS3R', 'copy' => 'Satu transaksi hanya memuat produk dari satu fasilitas TPS3R agar pemenuhan dan pembayaran daring (Midtrans) aman dan akurat.'],
                ] as $policyCard)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                        <h3 class="font-display text-sm font-extrabold text-slate-950">{{ $policyCard['title'] }}</h3>
                        <p class="mt-1.5 text-xs leading-5 text-slate-600">{{ $policyCard['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <x-public.action-band />
@endsection
