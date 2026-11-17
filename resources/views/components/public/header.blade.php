@props(['portalSettings' => []])

@php
    $navigation = [
        [
            'label' => 'Beranda',
            'route' => 'home',
            'url' => route('home'),
        ],
        [
            'label' => 'Jadwal & Peta',
            'route' => 'public.services',
            'url' => route('public.services'),
            'children' => [
                ['label' => 'Jadwal & Rute Armada', 'url' => route('public.services')],
                ['label' => 'Peta Titik TPS3R & Bank Sampah', 'url' => route('public.services') . '#fasilitas'],
                ['label' => 'Simulasi Tarif Retribusi', 'url' => route('public.services') . '#retribusi'],
            ],
        ],
        [
            'label' => 'Edukasi & Regulasi',
            'route' => 'public.education',
            'url' => route('public.education'),
            'children' => [
                ['label' => 'Artikel & Tips Pemilahan', 'url' => route('public.education')],
                ['label' => 'Dokumen & Regulasi Resmi DLH', 'url' => route('public.education') . '#dokumen-resmi'],
            ],
        ],
        [
            'label' => 'Marketplace',
            'route' => 'public.marketplace',
            'url' => route('public.marketplace'),
        ],
        [
            'label' => 'Unduh Aplikasi',
            'url' => route('home') . '#unduh-aplikasi',
        ],
    ];
    $operationalStatus = $portalSettings['operational_status_label'] ?? 'Pelayanan Normal';
@endphp

<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-md transition-shadow">
    <div class="mx-auto flex h-18 lg:h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Sisi Kiri: Logo Kota Madiun --}}
        <div class="flex items-center gap-3 shrink-0">
            <x-app-logo href="{{ route('home') }}" class="shrink-0" />
        </div>

        {{-- Tengah: Navigasi Minimalis & Elegan dengan Indikator Underline (Gaya JAKI) --}}
        <nav class="hidden h-full items-center gap-6 xl:gap-8 lg:flex" aria-label="Navigasi utama">
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
            <a
                href="{{ route('home') }}#cari-layanan"
                @click="if (window.location.pathname === '/' || window.location.pathname === '') { $event.preventDefault(); document.getElementById('portal-search-input')?.focus(); document.getElementById('cari-layanan')?.scrollIntoView({ behavior: 'smooth' }); }"
                title="Pencarian Cepat Layanan"
                class="flex size-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                aria-label="Pencarian layanan"
            >
                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </a>

            {{-- Language Selector (EN | ID) --}}
            <div class="hidden items-center gap-1 text-xs font-semibold text-slate-400 xl:flex">
                <span class="cursor-pointer px-1 text-slate-400 transition hover:text-slate-600" title="English (Coming Soon)">EN</span>
                <span class="text-slate-300">|</span>
                <span class="relative cursor-pointer px-1 font-bold text-slate-900" title="Bahasa Indonesia">
                    ID
                    <span class="absolute -bottom-1 inset-x-0 h-0.5 rounded-full bg-civic-600"></span>
                </span>
            </div>

            <div class="hidden h-5 w-px bg-slate-200 sm:block"></div>

            {{-- Auth Buttons --}}
            @auth
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
                    <span>Dashboard</span>
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:text-civic-700"
                >
                    Masuk
                </a>
                <a
                    href="{{ route('register') }}"
                    class="inline-flex items-center rounded-full bg-civic-700 px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-civic-800 focus:outline-none focus:ring-2 focus:ring-civic-500 focus:ring-offset-2"
                >
                    Daftar Warga
                </a>
            @endauth
        </div>

        {{-- Mobile Hamburger Nav (lg:hidden) --}}
        <details class="group relative ml-auto lg:hidden">
            <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:border-civic-300 hover:text-civic-800" aria-label="Buka navigasi">
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

                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="col-span-2 rounded-xl bg-civic-700 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-civic-800">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-civic-700 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-civic-800">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </details>
    </div>
</header>
