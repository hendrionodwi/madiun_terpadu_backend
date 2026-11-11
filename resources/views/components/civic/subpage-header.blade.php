@props([
    'eyebrow',
    'title',
    'description' => null,
    'breadcrumbLabel' => null,
    'breadcrumbRoute' => null,
    'current' => null,
    'icon' => 'squares-2x2',
])

<section {{ $attributes->class('relative isolate overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 p-5 shadow-[0_22px_55px_-38px_rgba(15,23,42,0.46)] backdrop-blur-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900/95') }}>
    <span class="pointer-events-none absolute -top-20 -right-16 -z-10 size-64 rounded-full bg-civic-100/70 blur-3xl dark:bg-civic-800/10" aria-hidden="true"></span>

    @if ($breadcrumbLabel && $breadcrumbRoute)
        <nav class="mb-4 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
            <a href="{{ route($breadcrumbRoute) }}" class="font-semibold transition hover:text-civic-700 dark:hover:text-civic-300" wire:navigate>{{ $breadcrumbLabel }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $current ?? $title }}</span>
        </nav>
    @endif

    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex min-w-0 items-start gap-4">
            <span class="civic-icon-tile size-12 shrink-0 rounded-2xl">
                <flux:icon :icon="$icon" class="size-6" />
            </span>
            <div class="min-w-0">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.16em] text-civic-700 dark:text-civic-300">{{ $eyebrow }}</p>
                <h1 class="mt-1 font-display text-2xl font-extrabold tracking-[-0.03em] text-slate-950 sm:text-3xl dark:text-white">@isset($heading){{ $heading }}@else{{ $title }}@endisset</h1>
                @if ($description)
                    <p class="mt-1.5 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>
                @endif
            </div>
        </div>

        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>
