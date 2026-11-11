@props(['title', 'description'])

<div {{ $attributes->class('rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center') }}>
    <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-civic-100 text-civic-700">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="m4 9 8 5 8-5"/></svg>
    </span>
    <h3 class="mt-4 font-display text-lg font-extrabold text-slate-950">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
</div>
