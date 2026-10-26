@props([
    'label',
    'value',
    'suffix' => null,
    'description' => null,
    'href' => null,
    'tone' => 'civic',
])

@php
    $toneClasses = match ($tone) {
        'amber' => 'bg-amber-100 text-amber-700 ring-amber-200/70 dark:bg-amber-950/60 dark:text-amber-300 dark:ring-amber-900',
        'sky' => 'bg-sky-100 text-sky-700 ring-sky-200/70 dark:bg-sky-950/60 dark:text-sky-300 dark:ring-sky-900',
        'violet' => 'bg-violet-100 text-violet-700 ring-violet-200/70 dark:bg-violet-950/60 dark:text-violet-300 dark:ring-violet-900',
        'rose' => 'bg-rose-100 text-rose-700 ring-rose-200/70 dark:bg-rose-950/60 dark:text-rose-300 dark:ring-rose-900',
        default => 'bg-civic-100 text-civic-700 ring-civic-200/70 dark:bg-civic-900/60 dark:text-civic-300 dark:ring-civic-800',
    };

    $surfaceClasses = match ($tone) {
        'amber' => 'bg-linear-to-br from-white via-white to-amber-50/90 dark:from-slate-900 dark:via-slate-900 dark:to-amber-950/25',
        'sky' => 'bg-linear-to-br from-white via-white to-sky-50/90 dark:from-slate-900 dark:via-slate-900 dark:to-sky-950/25',
        'violet' => 'bg-linear-to-br from-white via-white to-violet-50/90 dark:from-slate-900 dark:via-slate-900 dark:to-violet-950/25',
        'rose' => 'bg-linear-to-br from-white via-white to-rose-50/90 dark:from-slate-900 dark:via-slate-900 dark:to-rose-950/25',
        default => 'bg-linear-to-br from-white via-white to-civic-50 dark:from-slate-900 dark:via-slate-900 dark:to-civic-950/30',
    };

    $accentClasses = match ($tone) {
        'amber' => 'from-amber-400 to-orange-500',
        'sky' => 'from-sky-400 to-blue-500',
        'violet' => 'from-violet-400 to-purple-500',
        'rose' => 'from-rose-400 to-red-500',
        default => 'from-civic-400 to-civic-700',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['civic-stat-card civic-stat-card-link group', $surfaceClasses]) }}>
        <span class="absolute inset-x-0 top-0 h-0.5 bg-linear-to-r {{ $accentClasses }}" aria-hidden="true"></span>
        <div class="relative z-10 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.11em] text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <p class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                    {{ $value }}
                    @if ($suffix)<span class="text-sm font-bold text-slate-500">{{ $suffix }}</span>@endif
                </p>
                @if ($description)<p class="mt-1.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</p>@endif
            </div>
            @isset($icon)
                <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl shadow-sm ring-1 transition duration-300 group-hover:-rotate-3 group-hover:scale-105 {{ $toneClasses }}">{{ $icon }}</span>
            @endisset
        </div>
        <span class="absolute right-4 bottom-3 flex size-6 translate-x-1 items-center justify-center rounded-full bg-white/80 text-civic-700 opacity-0 shadow-sm ring-1 ring-slate-200 transition duration-300 group-hover:translate-x-0 group-hover:opacity-100 dark:bg-slate-800 dark:text-civic-300 dark:ring-slate-700" aria-hidden="true">&rarr;</span>
    </a>
@else
    <div {{ $attributes->class(['civic-stat-card', $surfaceClasses]) }}>
        <span class="absolute inset-x-0 top-0 h-0.5 bg-linear-to-r {{ $accentClasses }}" aria-hidden="true"></span>
        <div class="relative z-10 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.11em] text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <p class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                    {{ $value }}
                    @if ($suffix)<span class="text-sm font-bold text-slate-500">{{ $suffix }}</span>@endif
                </p>
                @if ($description)<p class="mt-1.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</p>@endif
            </div>
            @isset($icon)
                <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl shadow-sm ring-1 {{ $toneClasses }}">{{ $icon }}</span>
            @endisset
        </div>
    </div>
@endif
