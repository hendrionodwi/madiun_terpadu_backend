@props(['portalSettings' => []])

@php
    $navigation = [
        [
            'label' => __('portal.navigation.home'),
            'route' => 'home',
            'url' => route('home'),
        ],
        [
            'label' => __('portal.navigation.services'),
            'route' => 'public.services',
            'url' => route('public.services'),
            'children' => [
                ['label' => __('portal.navigation.routes'), 'url' => route('public.services')],
                ['label' => __('portal.navigation.facilities'), 'url' => route('public.services') . '#fasilitas'],
                ['label' => __('portal.navigation.retribution'), 'url' => route('public.services') . '#retribusi'],
            ],
        ],
        [
            'label' => __('portal.navigation.education'),
            'route' => 'public.education',
            'url' => route('public.education'),
            'children' => [
                ['label' => __('portal.navigation.articles'), 'url' => route('public.education')],
                ['label' => __('portal.navigation.documents'), 'url' => route('public.education') . '#dokumen-resmi'],
            ],
        ],
        [
            'label' => __('portal.navigation.marketplace'),
            'route' => 'public.marketplace',
            'url' => route('public.marketplace'),
        ],
        [
            'label' => __('portal.navigation.download'),
            'url' => route('home') . '#unduh-aplikasi',
        ],
    ];
    $operationalStatus = $portalSettings['operational_status_label'] ?? 'Pelayanan Normal';
    $currentLocale = app()->getLocale();
@endphp

<header
    x-data="{ searchOpen: false }"
    @keydown.escape.window="searchOpen = false"
    class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-md transition-shadow"
>
    <div class="mx-auto flex h-18 lg:h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Sisi Kiri: Logo Kota Madiun --}}
        <div class="flex items-center gap-3 shrink-0">
            <x-app-logo href="{{ route('home') }}" class="shrink-0" />
        </div>

        {{-- Tengah: Navigasi Minimalis & Elegan dengan Indikator Underline (Gaya JAKI) --}}
        <nav class="hidden h-full items-center gap-6 xl:gap-8 lg:flex" aria-label="{{ __('portal.navigation.label') }}">
            @foreach ($navigation as $item)
                @php
                    $isActive = isset($item['route']) && (
                        request()->routeIs($item['route'])
                        || ($item['route'] === 'public.services' && (request()->routeIs('portal.jadwal-peta') || request()->routeIs('public.facilities.show')))
                        || ($item['route'] === 'public.education' && request()->routeIs('portal.articles.show'))
                        || ($item['route'] === 'public.marketplace' && request()->routeIs('marketplace.products.show'))
                    );
                    $hasChildren = ! empty($item['children']);
                @endphp

                @if ($hasChildren)
                    <div class="group relative flex h-full items-center" x-data="{ open: false }" @mouseleave="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            @mouseenter="open = true"
                            @class([
                                'relative flex h-full items-center gap-1.5 text-[14.5px] tracking-tight transition-colors',
                                'font-semibold text-slate-950' => $isActive,
                                'font-medium text-slate-600 hover:text-slate-950' => ! $isActive,
                            ])
                        >
                            <span>{{ $item['label'] }}</span>
                            <svg class="size-3.5 text-slate-400 transition-transform duration-200 group-hover:text-slate-700" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>

                            {{-- Indikator Underline Aktif --}}
                            @if ($isActive)
                                <span class="absolute bottom-0 inset-x-0 h-0.75 rounded-t-full bg-civic-600"></span>
                            @else
                                <span class="absolute bottom-0 inset-x-0 h-0.75 rounded-t-full bg-transparent transition-colors group-hover:bg-slate-200"></span>
                            @endif
                        </button>

                        {{-- Dropdown Card --}}
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 top-[calc(100%-2px)] z-50 min-w-56 rounded-2xl border border-slate-200/90 bg-white p-2 shadow-xl shadow-slate-900/10 backdrop-blur-sm"
                        >
                            @foreach ($item['children'] as $child)
                                <a
                                    href="{{ $child['url'] }}"
                                    class="flex items-center rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-civic-50 hover:text-civic-800"
                                >
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $item['url'] ?? route($item['route']) }}"
                        @class([
                            'group relative flex h-full items-center text-[14.5px] tracking-tight transition-colors',
                            'font-semibold text-slate-950' => $isActive,
                            'font-medium text-slate-600 hover:text-slate-950' => ! $isActive,
                        ])
                    >
                        <span>{{ $item['label'] }}</span>

                        {{-- Indikator Underline Aktif --}}
                        @if ($isActive)
                            <span class="absolute bottom-0 inset-x-0 h-0.75 rounded-t-full bg-civic-600"></span>
                        @else
                            <span class="absolute bottom-0 inset-x-0 h-0.75 rounded-t-full bg-transparent transition-colors group-hover:bg-slate-200"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Sisi Kanan: Search, EN | ID, & Autentikasi / Dashboard --}}
        <div class="hidden items-center gap-3 sm:flex">
            {{-- Search Icon Trigger --}}
            <button
                type="button"
                @click="searchOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                title="{{ __('portal.search.title') }}"
                class="flex size-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                aria-label="{{ __('portal.search.open') }}"
            >
                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>

            {{-- Language Selector (EN | ID) --}}
            <div class="hidden items-center gap-1 text-xs font-semibold text-slate-400 xl:flex" aria-label="{{ __('portal.language.selector') }}">
                <form method="POST" action="{{ route('locale.update', 'en') }}">
                    @csrf
                    <button
                        type="submit"
                        title="{{ __('portal.language.english') }}"
                        @class([
                            'relative px-1 transition hover:text-slate-900',
                            'font-bold text-slate-900' => $currentLocale === 'en',
                            'text-slate-400' => $currentLocale !== 'en',
                        ])
                        @if ($currentLocale === 'en') aria-current="true" @endif
                    >
                        EN
                        @if ($currentLocale === 'en')
                            <span class="absolute -bottom-1 inset-x-0 h-0.5 rounded-full bg-civic-600"></span>
                        @endif
                    </button>
                </form>
                <span class="text-slate-300">|</span>
                <form method="POST" action="{{ route('locale.update', 'id') }}">
                    @csrf
                    <button
                        type="submit"
                        title="{{ __('portal.language.indonesian') }}"
                        @class([
                            'relative px-1 transition hover:text-slate-900',
                            'font-bold text-slate-900' => $currentLocale === 'id',
                            'text-slate-400' => $currentLocale !== 'id',
                        ])
                        @if ($currentLocale === 'id') aria-current="true" @endif
                    >
                        ID
                        @if ($currentLocale === 'id')
                            <span class="absolute -bottom-1 inset-x-0 h-0.5 rounded-full bg-civic-600"></span>
                        @endif
                    </button>
                </form>
            </div>

            <div class="hidden h-5 w-px bg-slate-200 sm:block"></div>

            {{-- Auth Buttons --}}
            @auth
                <a
                    href="{{ route('notifications.index') }}"
                    class="relative flex size-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-civic-50 hover:text-civic-800"
                    aria-label="Notifikasi{{ $unreadNotificationCount > 0 ? ' belum dibaca: '.$unreadNotificationCount : '' }}"
                    title="Notifikasi"
                >
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></svg>
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute right-0 top-0 flex min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.6rem] font-black leading-4 text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                    @endif
                </a>
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-civic-700 px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-civic-800 focus:outline-none focus:ring-2 focus:ring-civic-500 focus:ring-offset-2"
                >
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="7" height="9" x="3" y="3" rx="1" />
                        <rect width="7" height="5" x="14" y="3" rx="1" />
                        <rect width="7" height="9" x="14" y="12" rx="1" />
                        <rect width="7" height="5" x="3" y="16" rx="1" />
                    </svg>
                    <span>{{ __('portal.auth.dashboard') }}</span>
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:text-civic-700"
                >
                    {{ __('portal.auth.login') }}
                </a>
                <a
                    href="{{ route('register') }}"
                    class="inline-flex items-center rounded-full bg-civic-700 px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-civic-800 focus:outline-none focus:ring-2 focus:ring-civic-500 focus:ring-offset-2"
                >
                    {{ __('portal.auth.register') }}
                </a>
            @endauth
        </div>

        {{-- Mobile Hamburger Nav (lg:hidden) --}}
        <details class="group relative ml-auto lg:hidden">
            <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:border-civic-300 hover:text-civic-800" aria-label="{{ __('portal.navigation.open') }}">
                <svg class="size-5 transition group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
                <svg class="hidden size-5 transition group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </summary>

            <div class="absolute right-0 mt-2 w-[min(20rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-3 shadow-xl shadow-slate-900/10">
                <div class="space-y-1">
                    @foreach ($navigation as $item)
                        @php
                            $isActiveMobile = isset($item['route']) && request()->routeIs($item['route']);
                        @endphp
                        <a
                            href="{{ $item['url'] ?? route($item['route']) }}"
                            @class([
                                'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-civic-50 text-civic-800 border-l-3 border-civic-600' => $isActiveMobile,
                                'text-slate-700 hover:bg-slate-100' => ! $isActiveMobile,
                            ])
                        >
                            <span>{{ $item['label'] }}</span>
                            @if (! empty($item['children']))
                                <span class="text-xs text-slate-400">▾</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <button
                    type="button"
                    @click="searchOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                    class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-civic-300 hover:bg-civic-50 hover:text-civic-800"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    {{ __('portal.search.title') }}
                </button>

                <div class="mt-3 flex items-center justify-center gap-2 border-t border-slate-100 pt-3" aria-label="{{ __('portal.language.selector') }}">
                    @foreach (['id' => 'ID', 'en' => 'EN'] as $locale => $label)
                        <form method="POST" action="{{ route('locale.update', $locale) }}">
                            @csrf
                            <button
                                type="submit"
                                @class([
                                    'rounded-lg px-3 py-1.5 text-xs font-bold transition',
                                    'bg-civic-700 text-white' => $currentLocale === $locale,
                                    'bg-slate-100 text-slate-600 hover:bg-slate-200' => $currentLocale !== $locale,
                                ])
                                @if ($currentLocale === $locale) aria-current="true" @endif
                            >
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                    @auth
                        <a href="{{ route('notifications.index') }}" class="col-span-2 flex items-center justify-between rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-civic-50 hover:text-civic-800">
                            <span>Notifikasi</span>
                            @if ($unreadNotificationCount > 0)
                                <span class="rounded-full bg-red-600 px-2 py-0.5 text-xs text-white">{{ $unreadNotificationCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('dashboard') }}" class="col-span-2 rounded-xl bg-civic-700 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-civic-800">
                            {{ __('portal.auth.open_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                            {{ __('portal.auth.login') }}
                        </a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-civic-700 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-civic-800">
                            {{ __('portal.auth.register_short') }}
                        </a>
                    @endauth
                </div>
            </div>
        </details>
    </div>

    <div
        x-show="searchOpen"
        x-cloak
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[70] flex items-start justify-center bg-slate-950/45 px-4 pt-24 backdrop-blur-sm sm:pt-32"
        role="dialog"
        aria-modal="true"
        aria-labelledby="portal-search-title"
    >
        <button type="button" @click="searchOpen = false" class="absolute inset-0" aria-label="{{ __('portal.search.close') }}"></button>

        <div
            x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="relative w-full max-w-2xl rounded-3xl border border-white/60 bg-white p-5 shadow-2xl shadow-slate-950/20 sm:p-7"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="portal-search-title" class="font-display text-xl font-extrabold text-slate-950">{{ __('portal.search.title') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('portal.search.description') }}</p>
                </div>
                <button type="button" @click="searchOpen = false" class="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" aria-label="{{ __('portal.search.close') }}">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="GET" action="{{ route('public.search') }}" class="mt-5 flex flex-col gap-2 sm:flex-row">
                <label class="flex min-h-12 flex-1 items-center rounded-xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-civic-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-civic-100">
                    <svg class="size-4.5 shrink-0 text-civic-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                    <input
                        x-ref="searchInput"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        maxlength="100"
                        placeholder="{{ __('portal.search.placeholder') }}"
                        class="w-full border-0 bg-transparent px-3 text-sm text-slate-950 outline-none placeholder:text-slate-400"
                    >
                </label>
                <button type="submit" class="min-h-12 rounded-xl bg-civic-700 px-6 text-sm font-bold text-white transition hover:bg-civic-800 focus:outline-none focus:ring-2 focus:ring-civic-500 focus:ring-offset-2">
                    {{ __('portal.search.button') }}
                </button>
            </form>
        </div>
    </div>
</header>
