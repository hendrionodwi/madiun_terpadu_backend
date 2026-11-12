@props([
    'profileData',
])

@php
    $badges = $profileData['badges'] ?? [];
    $metrics = $profileData['metrics'] ?? [];
@endphp

<section {{ $attributes->class('overflow-hidden rounded-3xl border border-slate-200/85 bg-white shadow-[0_22px_55px_-38px_rgba(15,23,42,0.42)] dark:border-slate-800 dark:bg-slate-900') }}>
    {{-- Cover Banner Civic Emerald --}}
    <div class="relative h-24 overflow-hidden bg-linear-to-r from-civic-950 via-civic-800 to-civic-600 sm:h-28">
        <div class="civic-hero-grid opacity-15" aria-hidden="true"></div>
        <span class="pointer-events-none -right-12 -bottom-14 absolute size-44 rounded-full border border-white/10 bg-white/5" aria-hidden="true"></span>
    </div>

    {{-- Identity + Floating Metrics Row --}}
    <div class="px-5 pb-6 sm:px-7">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Overlapping Rounded-Square Avatar --}}
                <div class="-mt-12 flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-civic-50 shadow-md ring-1 ring-slate-200/70 sm:-mt-14 sm:size-28 dark:border-slate-900 dark:bg-civic-950 dark:ring-slate-700">
                    @if (! empty($profileData['avatar_url']))
                        <img
                            src="{{ $profileData['avatar_url'] }}"
                            alt="{{ $profileData['display_name'] }}"
                            class="size-full object-cover"
                        >
                    @else
                        <span class="font-display text-3xl font-extrabold tracking-tight text-civic-700 sm:text-4xl dark:text-civic-300">
                            {{ $profileData['initials'] }}
                        </span>
                    @endif
                </div>

                <div class="min-w-0 pt-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="truncate font-display text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl dark:text-white">
                            {{ $profileData['display_name'] }}
                        </h2>
                        <span class="inline-flex items-center rounded-full border border-civic-200 bg-civic-50 px-2.5 py-0.5 text-[0.65rem] font-extrabold uppercase tracking-wider text-civic-800 dark:border-civic-800 dark:bg-civic-950/80 dark:text-civic-200">
                            {{ $profileData['role_label'] }}
                        </span>
                    </div>

                    <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">
                        {{ $profileData['email'] }}
                        @if (! empty($profileData['phone_number']))
                            <span class="mx-1.5 text-slate-300 dark:text-slate-600">&bull;</span>
                            <span>{{ $profileData['phone_number'] }}</span>
                        @endif
                    </p>

                    @if (count($badges) > 0)
                        <div class="mt-2.5 flex flex-wrap items-center gap-2">
                            @foreach ($badges as $badge)
                                @php
                                    $toneClass = match ($badge['tone'] ?? 'neutral') {
                                        'emerald' => 'border-civic-200 bg-civic-50 text-civic-800 dark:border-civic-800 dark:bg-civic-950/70 dark:text-civic-300',
                                        'info' => 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-800 dark:bg-sky-950/70 dark:text-sky-300',
                                        'amber' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/70 dark:text-amber-300',
                                        'danger' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800 dark:bg-rose-950/70 dark:text-rose-300',
                                        default => 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $toneClass }}">
                                    {{ $badge['label'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Floating Mini KPI Card --}}
            @if (count($metrics) > 0)
                <div class="grid shrink-0 grid-cols-3 divide-x divide-slate-200/80 rounded-2xl border border-slate-200/90 bg-slate-50/90 px-2 py-3 shadow-xs dark:divide-slate-800 dark:border-slate-700/80 dark:bg-slate-950/80">
                    @foreach ($metrics as $metric)
                        @php
                            $valTone = match ($metric['tone'] ?? 'emerald') {
                                'emerald' => 'text-civic-700 dark:text-civic-300',
                                'info' => 'text-sky-600 dark:text-sky-400',
                                'amber' => 'text-amber-600 dark:text-amber-400',
                                default => 'text-slate-900 dark:text-white',
                            };
                        @endphp
                        <div class="px-3 text-center sm:px-5">
                            <p class="font-display text-base font-extrabold tabular-nums sm:text-lg {{ $valTone }}">
                                {{ $metric['value'] }}
                            </p>
                            <p class="mt-0.5 text-[0.65rem] font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                                {{ $metric['label'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
