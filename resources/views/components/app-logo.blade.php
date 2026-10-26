@props([
    'sidebar' => false,
])

<a {{ $attributes->class([
    'group flex min-w-0 items-center gap-3 rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-civic-500 focus-visible:ring-offset-2',
    'px-1 py-1' => $sidebar,
]) }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-civic-200 dark:bg-slate-900 dark:ring-civic-800">
        <x-app-logo-icon class="size-8" />
    </span>

    <span class="min-w-0 leading-tight">
        <span class="block text-[0.62rem] font-extrabold uppercase tracking-[0.16em] text-civic-700 dark:text-civic-300">
            Kota Madiun
        </span>
        <span class="block truncate font-display text-sm font-extrabold text-slate-950 dark:text-white">
            Madiun Terpadu
        </span>
    </span>
</a>
