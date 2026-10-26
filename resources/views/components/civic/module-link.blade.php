@props([
    'href',
    'title',
    'description',
    'meta' => null,
    'tone' => 'civic',
])

@php
    $toneClasses = match ($tone) {
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'violet' => 'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300',
        'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        default => 'bg-civic-100 text-civic-700 dark:bg-civic-900/60 dark:text-civic-300',
    };

    $surfaceClasses = match ($tone) {
        'amber' => 'hover:bg-amber-50/70 dark:hover:bg-amber-950/20',
        'sky' => 'hover:bg-sky-50/70 dark:hover:bg-sky-950/20',
        'violet' => 'hover:bg-violet-50/70 dark:hover:bg-violet-950/20',
        'rose' => 'hover:bg-rose-50/70 dark:hover:bg-rose-950/20',
        default => 'hover:bg-civic-50/80 dark:hover:bg-civic-950/25',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->class(['civic-card-interactive group flex h-full flex-col p-4.5', $surfaceClasses]) }}>
    <div class="relative z-10 flex items-start justify-between gap-3">
        <span class="flex size-11 items-center justify-center rounded-2xl shadow-sm transition duration-300 group-hover:-rotate-3 group-hover:scale-105 {{ $toneClasses }}">
            {{ $icon ?? '' }}
        </span>
        <span class="flex size-7 items-center justify-center rounded-full bg-slate-50 text-civic-700 ring-1 ring-slate-200 transition duration-300 group-hover:translate-x-0.5 group-hover:bg-civic-700 group-hover:text-white group-hover:ring-civic-700 dark:bg-slate-800 dark:text-civic-300 dark:ring-slate-700" aria-hidden="true">&rarr;</span>
    </div>
    <h3 class="relative z-10 mt-4 font-display text-sm font-extrabold text-slate-950 dark:text-white">{{ $title }}</h3>
    <p class="relative z-10 mt-1.5 grow text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @if ($meta)
        <p class="relative z-10 mt-3 inline-flex w-fit rounded-full bg-civic-50 px-2.5 py-1 text-[0.62rem] font-extrabold uppercase tracking-wider text-civic-700 ring-1 ring-civic-100 dark:bg-civic-950/50 dark:text-civic-300 dark:ring-civic-900">{{ $meta }}</p>
    @endif
</a>
