@props([
    'tabs' => [],
])

<nav aria-label="{{ __('Navigasi profil dan pengaturan') }}" class="-mx-1 flex items-center gap-1 overflow-x-auto border-b border-slate-200/90 px-5 pt-2 sm:px-7 dark:border-slate-800">
    @foreach ($tabs as $tab)
        @php
            $isActive = (bool) ($tab['active'] ?? false);
        @endphp
        <a
            href="{{ $tab['route'] }}"
            wire:navigate
            @if ($isActive) aria-current="page" @endif
            class="inline-flex min-h-11 shrink-0 items-center gap-2 border-b-2 px-3.5 py-3 text-sm transition whitespace-nowrap {{ $isActive
                ? 'border-civic-700 font-bold text-civic-700 dark:border-civic-400 dark:text-civic-300'
                : 'border-transparent font-medium text-slate-500 hover:border-slate-300 hover:text-slate-800 dark:text-slate-400 dark:hover:border-slate-700 dark:hover:text-slate-200' }}"
        >
            @if (! empty($tab['icon']))
                <flux:icon :icon="$tab['icon']" class="size-4 shrink-0" />
            @endif
            <span>{{ $tab['label'] }}</span>
        </a>
    @endforeach
</nav>
