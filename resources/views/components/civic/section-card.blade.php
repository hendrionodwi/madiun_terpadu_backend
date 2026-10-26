@props([
    'title',
    'description' => null,
    'padded' => true,
])

<section {{ $attributes->class('civic-card overflow-hidden transition duration-300 hover:shadow-[0_24px_60px_-42px_rgba(15,23,42,0.5)]') }}>
    <header class="civic-section-header">
        <div>
            <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>

        @isset($action)
            <div class="shrink-0">{{ $action }}</div>
        @endisset
    </header>

    <div @class(['p-5' => $padded])>
        {{ $slot }}
    </div>
</section>
