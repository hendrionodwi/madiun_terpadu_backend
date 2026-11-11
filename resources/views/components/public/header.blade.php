@props(['portalSettings' => []])

@php
    $navigation = [
        ['label' => 'Beranda', 'route' => 'home'],
        ['label' => 'Jadwal & Peta', 'route' => 'public.services'],
        ['label' => 'Edukasi', 'route' => 'public.education'],
        ['label' => 'Marketplace', 'route' => 'public.marketplace'],
    ];
    $operationalStatus = $portalSettings['operational_status_label'] ?? 'Pelayanan Wilayah Normal';
@endphp

<div class="border-b border-civic-900 bg-civic-950 text-xs text-civic-100">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-4 py-2 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 font-medium">
            <svg class="size-3.5 shrink-0 text-civic-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            </svg>
            <span>Portal Resmi Pemerintah Kota Madiun · Dinas Lingkungan Hidup</span>
        </div>
        <div class="hidden items-center gap-2 text-civic-200 sm:flex">
            <span class="size-1.5 rounded-full bg-emerald-400"></span>
            <span>{{ $operationalStatus }}</span>
        </div>
    </div>
</div>

<header class="sticky top-0 z-40 border-b border-slate-200/85 bg-white/95 backdrop-blur-md">
    <div class="mx-auto flex min-h-17 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        <x-app-logo href="{{ route('home') }}" class="shrink-0" />

        <nav class="ml-auto hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
            @foreach ($navigation as $item)
                @php
                    $isActive = request()->routeIs($item['route'])
                        || ($item['route'] === 'public.services' && request()->routeIs('portal.jadwal-peta'))
                        || ($item['route'] === 'public.education' && request()->routeIs('portal.articles.show'));
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'rounded-lg px-3.5 py-2 text-sm font-semibold transition',
                        'bg-civic-50 text-civic-800 ring-1 ring-inset ring-civic-200' => $isActive,
                        'text-slate-600 hover:bg-slate-100/80 hover:text-slate-950' => ! $isActive,
                    ])
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 sm:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-civic-700 px-4 text-sm font-bold text-white shadow-xs hover:bg-civic-800">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-200 bg-white px-3.5 text-sm font-bold text-slate-700 hover:border-civic-300 hover:bg-civic-50 hover:text-civic-900">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="inline-flex min-h-10 items-center rounded-lg bg-civic-700 px-4 text-sm font-bold text-white shadow-xs hover:bg-civic-800">
                    Daftar warga
                </a>
            @endauth
        </div>

        <details class="group relative ml-auto lg:hidden">
            <summary class="flex size-11 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 hover:border-civic-300 hover:text-civic-800" aria-label="Buka navigasi">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
            </summary>
            <div class="absolute right-0 mt-2 w-[min(19rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}" @class(['block rounded-xl px-4 py-3 text-sm font-semibold', 'bg-civic-100 text-civic-800' => request()->routeIs($item['route']), 'text-slate-700 hover:bg-slate-100' => ! request()->routeIs($item['route'])])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <div class="mt-2 grid grid-cols-2 gap-2 border-t border-slate-100 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="col-span-2 rounded-xl bg-civic-700 px-4 py-3 text-center text-sm font-bold text-white">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-bold text-slate-700">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-civic-700 px-4 py-3 text-center text-sm font-bold text-white">Daftar</a>
                    @endauth
                </div>
            </div>
        </details>
    </div>
</header>
