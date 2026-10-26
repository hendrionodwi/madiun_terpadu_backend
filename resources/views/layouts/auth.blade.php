<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-civic-canvas font-sans text-civic-ink antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="grid min-h-screen lg:grid-cols-12">
            <aside class="relative hidden overflow-hidden bg-civic-900 px-10 py-10 text-white lg:col-span-5 lg:flex lg:flex-col xl:px-14 xl:py-12">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_82%_18%,rgba(101,184,167,0.28),transparent_28%),radial-gradient(circle_at_8%_92%,rgba(217,119,6,0.16),transparent_25%)]"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.08] [background-image:radial-gradient(circle,#fff_1px,transparent_1px)] [background-size:22px_22px]"></div>

                <a href="{{ route('home') }}" class="relative z-10 inline-flex items-center gap-3 text-white" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-white shadow-lg shadow-civic-950/30">
                        <x-app-logo-icon class="size-8" />
                    </span>
                    <span>
                        <span class="block font-display text-lg font-extrabold leading-tight">Madiun Terpadu</span>
                        <span class="block text-[0.65rem] font-bold uppercase tracking-[0.18em] text-civic-200">Kota Madiun</span>
                    </span>
                </a>

                <div class="relative z-10 my-auto max-w-lg py-12">
                    <p class="civic-kicker">
                        <span class="size-1.5 rounded-full bg-civic-300"></span>
                        Portal layanan lingkungan
                    </p>
                    <h1 class="mt-5 font-display text-4xl font-extrabold leading-[1.08] tracking-[-0.04em] xl:text-5xl">
                        Satu akses untuk Madiun yang lebih bersih.
                    </h1>
                    <p class="mt-5 max-w-md text-sm leading-7 text-civic-100/85 xl:text-base">
                        Kelola layanan sampah, aktivitas TPS3R, laporan lingkungan, dan pengaduan masyarakat dalam satu ekosistem terhubung.
                    </p>

                    <div class="mt-9 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                            <svg class="size-6 text-civic-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v9h13v-9M9 19v-5h6v5"/></svg>
                            <p class="mt-3 text-sm font-bold">Layanan lintas peran</p>
                            <p class="mt-1 text-xs leading-5 text-civic-100/70">Warga, petugas, TPS3R, pemrakarsa, dan DLH.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                            <svg class="size-6 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 18V9m5 9V5m5 13v-7m5 7V3"/><path d="M2 21h20"/></svg>
                            <p class="mt-3 text-sm font-bold">Data operasional terpadu</p>
                            <p class="mt-1 text-xs leading-5 text-civic-100/70">Pantau progres layanan dan tindak lanjut secara jelas.</p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-5 text-xs text-civic-100/65">
                    <span>Dinas Lingkungan Hidup</span>
                    <span>Kota Madiun · {{ now()->year }}</span>
                </div>
            </aside>

            <main class="flex min-h-screen flex-col px-5 py-5 sm:px-8 sm:py-7 lg:col-span-7 lg:px-12 xl:px-20">
                <div class="flex items-center justify-between lg:justify-end">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 lg:hidden" wire:navigate>
                        <span class="flex size-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-civic-200 dark:bg-slate-900 dark:ring-slate-800">
                            <x-app-logo-icon class="size-7" />
                        </span>
                        <span>
                            <span class="block font-display text-sm font-extrabold">Madiun Terpadu</span>
                            <span class="block text-[0.58rem] font-bold uppercase tracking-[0.16em] text-civic-700 dark:text-civic-300">Kota Madiun</span>
                        </span>
                    </a>
                    <a href="{{ route('home') }}" class="civic-link rounded-lg px-2 py-1" wire:navigate>
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        Portal publik
                    </a>
                </div>

                <div class="mx-auto my-auto w-full max-w-[30rem] py-8">
                    <div class="rounded-[1.75rem] border border-slate-200/80 bg-white p-6 shadow-[0_24px_70px_-42px_rgba(15,23,42,0.38)] sm:p-9 dark:border-slate-800 dark:bg-slate-900">
                        {{ $slot }}
                    </div>
                </div>

                <p class="text-center text-xs text-slate-500 dark:text-slate-500">
                    © {{ now()->year }} Dinas Lingkungan Hidup Kota Madiun
                </p>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
