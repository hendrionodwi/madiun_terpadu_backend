@props([
    'ideal',
    'ratio',
    'formats' => 'JPG, PNG, atau WebP',
    'maxSize',
    'minimum' => null,
    'note' => null,
])

<div data-image-upload-guidance {{ $attributes->class('mt-2 flex items-start gap-2 rounded-xl border border-civic-100 bg-civic-50/70 px-3 py-2.5 text-[0.7rem] leading-5 font-normal text-slate-600 dark:border-civic-900 dark:bg-civic-950/45 dark:text-slate-300') }}>
    <svg class="mt-0.5 size-4 shrink-0 fill-none stroke-civic-600 stroke-[1.8] dark:stroke-civic-300" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" stroke-linecap="round" stroke-linejoin="round" />
        <path d="m7 16 3.2-3.2 2.4 2.4 1.4-1.4 3 3M15.5 8.5h.01" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <p>
        <span class="font-extrabold text-civic-800 dark:text-civic-200">Rekomendasi:</span>
        {{ $ideal }} · rasio {{ $ratio }}
        @if ($minimum)
            · minimum {{ $minimum }}
        @endif
        · {{ $formats }} · maks. {{ $maxSize }}.
        @if ($note)
            <span class="text-slate-500 dark:text-slate-400">{{ $note }}</span>
        @endif
    </p>
</div>
