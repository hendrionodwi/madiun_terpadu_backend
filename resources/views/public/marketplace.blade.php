@extends('layouts.public', ['title' => __('portal.marketplace.title')])

@section('content')
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 lg:px-8 portal-settle">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <nav class="flex items-center gap-2 text-slate-500" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="font-medium hover:text-civic-700">{{ __('portal.navigation.home') }}</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-bold text-slate-900">{{ __('portal.marketplace.breadcrumb') }}</span>
                </nav>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-50 px-3 py-1 font-bold text-civic-800 ring-1 ring-civic-200/80">
                    <span class="size-1.5 rounded-full bg-civic-600"></span>
                    {{ __('portal.marketplace.public_catalog') }}
                </span>
            </div>

            <div class="mt-4">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.marketplace.kicker') }}</p>
                <h1 class="mt-1.5 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ __('portal.marketplace.heading') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                    {{ __('portal.marketplace.description') }}
                </p>
            </div>

            <div class="mt-5 flex flex-col justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 sm:flex-row sm:items-center">
                <p>
                    <span class="font-bold text-slate-800">{{ __('portal.marketplace.public_access') }}</span> {{ __('portal.marketplace.public_access_description') }}
                </p>
                @auth
                    @if (auth()->user()->isWarga())
                        <div class="flex shrink-0 items-center gap-4 font-bold text-civic-700">
                            <a href="{{ route('marketplace.cart') }}" class="hover:text-civic-900">{{ __('portal.marketplace.cart') }}</a>
                            <a href="{{ route('marketplace.orders') }}" class="hover:text-civic-900">{{ __('portal.marketplace.my_orders') }}</a>
                        </div>
                    @else
                        <div class="flex shrink-0 items-center gap-4 font-bold text-civic-700">
                            <a href="{{ route('dashboard') }}" class="hover:text-civic-900">{{ __('portal.marketplace.your_dashboard') }}</a>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="shrink-0 font-bold text-civic-700 hover:text-civic-900">{{ __('portal.marketplace.login_to_buy') }}</a>
                @endauth
            </div>

            <div class="mt-6 grid gap-6 overflow-hidden rounded-2xl bg-gradient-to-br from-civic-800 via-civic-900 to-[#0d3b33] p-6 text-white shadow-sm lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:p-8">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-civic-100">
                        {{ __('portal.marketplace.hero_badge') }}
                    </span>
                    <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('portal.marketplace.hero_title') }}</h2>
                    <p class="mt-2.5 max-w-xl text-sm leading-6 text-civic-100/85">
                        {{ __('portal.marketplace.hero_copy') }}
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('public.education') }}" class="inline-flex min-h-10 items-center rounded-xl bg-white px-4 text-xs font-extrabold text-civic-900 hover:bg-civic-50 sm:text-sm">
                            {{ __('portal.marketplace.learn_processing') }}
                        </a>
                        <a href="{{ route('public.services') }}" class="inline-flex min-h-10 items-center rounded-xl border border-white/25 bg-white/10 px-4 text-xs font-bold text-white hover:bg-white/15 sm:text-sm">
                            {{ __('portal.marketplace.find_facility') }}
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-5 text-civic-ink shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 text-xs">
                        <span class="font-extrabold uppercase tracking-wider text-slate-500">{{ __('portal.marketplace.processing_focus') }}</span>
                        <span class="font-bold text-civic-700">{{ __('portal.marketplace.integrated') }}</span>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-civic-100 text-civic-700">
                                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path d="M8 13c4 0 6-2 7-6 2 5 0 10-5 11" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-extrabold text-slate-900">{{ __('portal.marketplace.organic_products') }}</p>
                                <p class="text-xs text-slate-500">{{ __('portal.marketplace.managed_by', ['count' => $stores->count()]) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-civic-100 text-civic-700">
                                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z" /><path d="M9 9V6a3 3 0 0 1 6 0v3" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-extrabold text-slate-900">{{ __('portal.marketplace.recycled_crafts') }}</p>
                                <p class="text-xs text-slate-500">{{ __('portal.marketplace.recycled_crafts_description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                        <span class="text-slate-500">{{ __('portal.marketplace.official_payment') }}</span>
                        <span class="font-bold text-slate-800">{{ __('portal.marketplace.payment_description') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section data-ajax-zone="marketplace" data-ajax-targets="#marketplaceCategoryPills,#marketplaceResultSummary,#marketplaceResults" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @php
            $sortOptions = [
                'terbaru' => __('portal.marketplace.sort').': '.__('portal.marketplace.sort_newest'),
                'termurah' => __('portal.marketplace.sort_lowest'),
                'termahal' => __('portal.marketplace.sort_highest'),
            ];
            $storeOptions = collect(['' => __('portal.marketplace.all_sellers')])
                ->merge($stores->mapWithKeys(fn ($store) => [$store->public_id => $store->nama_tps3r]))
                ->all();
            $priceOptions = [
                '' => __('portal.marketplace.all_prices'),
                'under_25k' => __('portal.marketplace.price_under'),
                '25k_50k' => __('portal.marketplace.price_middle'),
                'above_50k' => __('portal.marketplace.price_above'),
            ];
        @endphp

        <form id="marketplaceFilterForm" method="GET" action="{{ route('public.marketplace') }}" data-ajax-form data-ajax-targets="#marketplaceCategoryPills,#marketplaceResultSummary,#marketplaceResults" class="relative z-20 rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <div class="grid gap-3 md:grid-cols-[1fr_13.5rem_auto]">
                <label class="relative block">
                    <span class="sr-only">{{ __('portal.marketplace.search_label') }}</span>
                    <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                    <input
                        id="marketplaceSearchInput"
                        name="search"
                        value="{{ $filters['search'] }}"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-civic-500 focus:bg-white focus:ring-4 focus:ring-civic-100"
                        placeholder="{{ __('portal.marketplace.search_placeholder') }}"
                    >
                </label>

                <x-public.select-filter
                    name="sort"
                    :value="$filters['sort']"
                    :options="$sortOptions"
                    :placeholder="__('portal.marketplace.sort')"
                    height-class="h-11"
                    :align-right="true"
                />

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center rounded-xl bg-civic-700 px-5 text-sm font-bold text-white hover:bg-civic-800 md:flex-none">{{ __('portal.common.search') }}</button>
                    <a href="{{ route('public.marketplace') }}" data-ajax-link class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 hover:border-slate-300 hover:bg-slate-50">{{ __('portal.common.reset') }}</a>
                </div>
            </div>

            <div id="marketplaceCategoryPills" class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                <span class="mr-1 text-xs font-semibold text-slate-500">{{ __('portal.marketplace.categories') }}</span>
                <a
                    href="{{ route('public.marketplace', array_filter(['search' => $filters['search'], 'store' => $filters['store'], 'price_range' => $filters['price_range'], 'sort' => $filters['sort'] !== 'terbaru' ? $filters['sort'] : null])) }}"
                    data-ajax-link
                    @class([
                        'rounded-full px-3.5 py-1.5 text-xs font-bold transition',
                        'bg-civic-700 text-white shadow-2xs' => $filters['category'] === '',
                        'border border-slate-200 bg-slate-50 text-slate-700 hover:border-civic-300 hover:bg-civic-50 hover:text-civic-800' => $filters['category'] !== '',
                    ])
                >
                    {{ __('portal.search.view_all') }} ({{ number_format($totalAvailableProducts, 0, ',', '.') }})
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
                        :placeholder="__('portal.marketplace.all_sellers')"
                        height-class="h-10.5"
                    />

                    <x-public.select-filter
                        name="price_range"
                        :value="$filters['price_range']"
                        :options="$priceOptions"
                        :placeholder="__('portal.marketplace.all_prices')"
                        height-class="h-10.5"
                    />
                </div>

                <input id="marketplaceCategoryInput" type="hidden" name="category" value="{{ $filters['category'] }}">

                <p id="marketplaceResultSummary" class="text-xs text-slate-500">
                    {{ __('portal.marketplace.showing', ['count' => $products->total()]) }}
                </p>
            </div>
        </form>

        <div id="marketplaceResults" class="transition-opacity duration-200">

        @if ($featuredProduct)
            <div class="mt-10" data-scroll-fade>
                <div class="flex items-center justify-between gap-4">
                    <h2 class="flex items-center gap-2 font-display text-lg font-extrabold text-slate-950">
                        <svg class="size-5 text-civic-700" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" /></svg>
                        {{ __('portal.marketplace.featured') }}
                    </h2>
                    <span class="text-xs font-semibold text-slate-500">{{ __('portal.marketplace.popular') }}</span>
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
                        <span class="absolute top-4 left-4 rounded-md bg-[#172536] px-2.5 py-1 text-[0.7rem] font-extrabold uppercase tracking-wider text-white">{{ __('portal.marketplace.featured') }}</span>
                    </div>

                    <div class="flex flex-col justify-between p-6 sm:p-8">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-full bg-civic-100 px-2.5 py-0.5 font-bold text-civic-800">{{ str($featuredProduct->kategori)->replace('_', ' ')->title() }}</span>
                                <span class="text-slate-400">·</span>
                                <span class="font-semibold text-slate-600">
                                    {{ $featuredProduct->seller->pengelolaTps3rProfile?->nama_tps3r ?? $featuredProduct->seller->name }}
                                    @if ($featuredProduct->seller->pengelolaTps3rProfile?->kecamatan)
                                        ({{ __('portal.common.district_short') }} {{ $featuredProduct->seller->pengelolaTps3rProfile->kecamatan }})
                                    @endif
                                </span>
                            </div>

                            <h3 class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-950">{{ $featuredProduct->nama_produk }}</h3>
                            <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">
                                {{ $featuredProduct->deskripsi ?: __('portal.product.description_fallback') }}
                            </p>

                            <div class="mt-5 grid grid-cols-3 gap-4 border-y border-slate-100 py-4 text-xs tabular-nums">
                                <div>
                                    <p class="text-slate-500">{{ __('portal.marketplace.unit_price') }}</p>
                                    <p class="mt-1 font-display text-xl font-extrabold text-civic-800">Rp{{ number_format($featuredProduct->harga, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">{{ __('portal.marketplace.availability') }}</p>
                                    <p class="mt-1 text-base font-extrabold text-slate-900">{{ __('portal.marketplace.available', ['count' => number_format($featuredProduct->stok, 0, ',', '.')]) }}</p>
                                    <p class="font-semibold text-civic-700">{{ __('portal.marketplace.warehouse_stock') }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">{{ __('portal.marketplace.pickup') }}</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ __('portal.marketplace.pickup_methods') }}</p>
                                    <p class="text-slate-500">{{ $featuredProduct->seller->pengelolaTps3rProfile?->kelurahan ?? __('portal.common.city') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            @if ($featuredProduct->seller->pengelolaTps3rProfile)
                                <a href="{{ route('public.marketplace', ['store' => $featuredProduct->seller->pengelolaTps3rProfile->public_id]) }}" class="inline-flex min-h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 hover:border-civic-300 hover:text-civic-800">
                                    {{ __('portal.marketplace.view_store_products') }}
                                </a>
                            @endif
                            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-civic-700 px-5 text-sm font-bold text-white hover:bg-civic-800">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z" /><path d="M9 9V6a3 3 0 0 1 6 0v3" /></svg>
                                {{ auth()->check() ? __('portal.marketplace.buy_dashboard') : __('portal.marketplace.buy_login') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-12 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-2xl font-extrabold tracking-tight text-slate-950">{{ __('portal.marketplace.catalog_title') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('portal.marketplace.catalog_description') }}</p>
            </div>
            @if (collect($filters)->filter(fn ($v, $k) => $k !== 'sort' ? filled($v) : $v !== 'terbaru')->isNotEmpty())
                <a href="{{ route('public.marketplace') }}" data-marketplace-reset class="text-sm font-bold text-civic-700 hover:text-civic-900">{{ __('portal.marketplace.reset_all') }}</a>
            @endif
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-scroll-fade>
            @forelse ($products as $product)
                <x-public.product-card :product="$product" />
            @empty
                <x-public.empty-state class="sm:col-span-2 lg:col-span-3 xl:col-span-4" :title="__('portal.marketplace.empty_title')" :description="__('portal.marketplace.empty_description')" />
            @endforelse
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
        </div>
    </section>

    <section class="border-y border-civic-200/70 bg-civic-50/70 py-14" data-scroll-fade>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <span class="inline-flex rounded-md bg-civic-100 px-2.5 py-1 text-[0.7rem] font-extrabold uppercase tracking-wider text-civic-800">
                {{ __('portal.marketplace.partner_badge') }}
            </span>
            <h2 class="mt-3 font-display text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('portal.marketplace.partner_title') }}</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                {{ __('portal.marketplace.partner_description') }}
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
                                    {{ __('portal.marketplace.producing') }}
                                </span>
                            </div>
                            <h3 class="mt-4 font-display text-lg font-extrabold text-slate-950">{{ $store->nama_tps3r }}</h3>
                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ $store->kelurahan ? __('portal.common.village_short').' '.$store->kelurahan.', ' : '' }}{{ $store->kecamatan ? __('portal.common.district_short').' '.$store->kecamatan.', ' : '' }}{{ __('portal.common.city') }}
                            </p>
                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                                {{ $store->deskripsi ?: ($store->fokus_pengolahan ?: ($store->alamat ?: __('portal.marketplace.store_fallback'))) }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs font-bold">
                            <a href="{{ route('public.marketplace', ['store' => $store->public_id]) }}" data-ajax-link data-ajax-targets="#marketplaceCategoryPills,#marketplaceResultSummary,#marketplaceResults" data-ajax-scroll class="text-civic-700 hover:text-civic-900">
                                {{ $activeCount > 0 ? __('portal.marketplace.view_products', ['count' => $activeCount]) : __('portal.marketplace.view_store') }}
                            </a>
                            <a href="{{ route('public.facilities.show', $store) }}" class="text-slate-600 hover:text-civic-800">
                                {{ __('portal.marketplace.facility_profile') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <x-public.empty-state class="md:col-span-3" :title="__('portal.marketplace.partner_empty_title')" :description="__('portal.marketplace.partner_empty_description')" />
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
                    <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.marketplace.governance_title') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('portal.marketplace.governance_description') }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => __('portal.marketplace.policies.price_title'), 'copy' => __('portal.marketplace.policies.price_copy')],
                    ['title' => __('portal.marketplace.policies.points_title'), 'copy' => __('portal.marketplace.policies.points_copy')],
                    ['title' => __('portal.marketplace.policies.delivery_title'), 'copy' => __('portal.marketplace.policies.delivery_copy')],
                    ['title' => __('portal.marketplace.policies.cart_title'), 'copy' => __('portal.marketplace.policies.cart_copy')],
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
