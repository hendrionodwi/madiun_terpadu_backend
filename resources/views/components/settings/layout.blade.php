@props([
    'heading' => '',
    'subheading' => '',
    'activeTab' => null,
])

@php
    $resolvedTab = $activeTab ?? match (true) {
        request()->routeIs('password.edit') => 'password',
        request()->routeIs('security.edit') => 'security',
        request()->routeIs('appearance.edit') => 'appearance',
        default => 'profile',
    };
    $profileData = \App\Support\CivicProfilePresenter::forUser(auth()->user(), $resolvedTab);
@endphp

<div class="civic-page mx-auto w-full max-w-6xl">
    <x-civic.profile-cover :profile-data="$profileData" />

    <section class="civic-card overflow-hidden">
        <x-civic.profile-tabs :tabs="$profileData['tabs']" />

        <div class="p-5 sm:p-7">
            @if (filled($heading) || filled($subheading))
                <header class="mb-6 border-b border-slate-100 pb-4 dark:border-slate-800">
                    @if (filled($heading))
                        <h2 class="font-display text-lg font-extrabold tracking-tight text-slate-950 dark:text-white">{{ $heading }}</h2>
                    @endif
                    @if (filled($subheading))
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subheading }}</p>
                    @endif
                </header>
            @endif

            {{ $slot }}
        </div>
    </section>
</div>
