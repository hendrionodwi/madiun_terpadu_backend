@props([
    'eyebrow',
    'title',
    'description' => null,
])

<section
    x-data="{ spotlightX: 80, spotlightY: 18 }"
    x-on:pointermove="
        const bounds = $el.getBoundingClientRect();
        spotlightX = (($event.clientX - bounds.left) / bounds.width) * 100;
        spotlightY = (($event.clientY - bounds.top) / bounds.height) * 100;
    "
    x-on:pointerleave="spotlightX = 80; spotlightY = 18"
    x-bind:style="'--spotlight-x:' + spotlightX + '%; --spotlight-y:' + spotlightY + '%'"
    {{ $attributes->class('civic-hero') }}
>
    <div class="civic-hero-grid" aria-hidden="true"></div>

    <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-start gap-4 sm:items-center">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white p-2 shadow-lg shadow-civic-950/20 ring-1 ring-white/35 sm:size-16">
                <x-app-logo-icon class="size-full" />
            </span>

            <div class="min-w-0">
                <span class="civic-kicker">
                    <span class="size-1.5 rounded-full bg-emerald-300"></span>
                    {{ $eyebrow }}
                </span>
                <h1 class="civic-display mt-3">{{ $title }}</h1>
                @if ($description)
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-civic-100/90">{{ $description }}</p>
                @endif
            </div>
        </div>

        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2 transition duration-300 hover:translate-x-0.5">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @isset($meta)
        <div class="relative z-10 mt-5 border-t border-white/10 pt-4">
            {{ $meta }}
        </div>
    @endisset
</section>
