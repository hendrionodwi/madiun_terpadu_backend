@props(['summary' => []])

@php
    $tabs = [
        [
            'label' => 'Pengumuman',
            'route' => 'dashboard.admin.broadcasts',
            'active' => 'dashboard.admin.broadcasts*',
            'count' => $summary['active_announcements'] ?? null,
        ],
        [
            'label' => 'Artikel & Edukasi',
            'route' => 'dashboard.admin.articles',
            'active' => 'dashboard.admin.articles*',
            'count' => $summary['total_articles'] ?? null,
        ],
        [
            'label' => 'Dokumen Publik',
            'route' => 'dashboard.admin.documents',
            'active' => 'dashboard.admin.documents*',
            'count' => $summary['total_documents'] ?? null,
        ],
        [
            'label' => 'Tampilan Portal',
            'route' => 'dashboard.admin.portal-settings',
            'active' => 'dashboard.admin.portal-settings*',
            'count' => null,
        ],
    ];
@endphp

<nav aria-label="Navigasi manajemen konten" class="civic-filter-rail rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    @foreach ($tabs as $tab)
        <a
            href="{{ route($tab['route']) }}"
            wire:navigate
            @class([
                'inline-flex min-h-10 shrink-0 items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold transition sm:text-sm',
                'bg-civic-700 text-white shadow-sm' => request()->routeIs($tab['active']),
                'text-slate-600 hover:bg-civic-50 hover:text-civic-800 dark:text-slate-300 dark:hover:bg-civic-950/40 dark:hover:text-civic-200' => ! request()->routeIs($tab['active']),
            ])
        >
            {{ $tab['label'] }}
            @if ($tab['count'] !== null)
                <span @class([
                    'rounded-full px-2 py-0.5 text-xs font-extrabold',
                    'bg-white/15 text-white' => request()->routeIs($tab['active']),
                    'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => ! request()->routeIs($tab['active']),
                ])>{{ $tab['count'] }}</span>
            @endif
        </a>
    @endforeach
</nav>
