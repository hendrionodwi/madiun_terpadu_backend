<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh bg-civic-canvas text-slate-950 antialiased dark:bg-slate-950 dark:text-slate-100">
        <flux:sidebar sticky collapsible="mobile" class="civic-sidebar-shell border-e border-civic-100/90 backdrop-blur-xl dark:border-civic-950/80">
            <flux:sidebar.header class="civic-sidebar-header border-b border-civic-100/80 pb-4 dark:border-civic-900/70">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <div class="relative mx-2 my-4 flex items-center justify-between gap-3 overflow-hidden rounded-2xl border border-civic-100 bg-linear-to-br from-civic-50 via-white to-amber-50/60 px-3 py-3 shadow-[0_14px_34px_-28px_rgba(4,106,88,0.7)] transition duration-300 hover:-translate-y-0.5 hover:border-civic-200 hover:shadow-[0_18px_38px_-26px_rgba(4,106,88,0.55)] dark:border-civic-900 dark:from-civic-950/70 dark:via-slate-900 dark:to-amber-950/15 dark:hover:border-civic-800">
                <span class="absolute -right-5 -bottom-8 size-20 rounded-full bg-civic-200/40 blur-xl dark:bg-civic-700/15" aria-hidden="true"></span>
                <div class="relative min-w-0">
                    <p class="flex items-center gap-1.5 text-[0.6rem] font-bold uppercase tracking-[0.14em] text-civic-600 dark:text-civic-400">
                        <span class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.12)]"></span>
                        Ruang kerja
                    </p>
                    <p class="mt-1 truncate font-display text-xs font-extrabold text-civic-950 dark:text-civic-100">{{ auth()->user()->role->label() }}</p>
                </div>
                @unless (app()->isProduction())
                    <span class="relative shrink-0 rounded-full bg-amber-100 px-2 py-1 text-[0.58rem] font-extrabold uppercase tracking-wider text-amber-800 ring-1 ring-amber-200 dark:bg-amber-950/70 dark:text-amber-300 dark:ring-amber-900">
                        Mode uji
                    </span>
                @endunless
            </div>

            <flux:sidebar.nav>
                @if (auth()->user()->isAdminDlh())
                    <flux:sidebar.item icon="home" :href="route('dashboard.admin')" :current="request()->routeIs('dashboard.admin')" wire:navigate>{{ __('Monitoring Kota') }}</flux:sidebar.item>

                    <flux:sidebar.group :heading="__('Operasional')" expandable :expanded="request()->routeIs('dashboard.admin.complaints*', 'dashboard.admin.routes*', 'dashboard.admin.reports*')" class="grid">
                        <flux:sidebar.item icon="chat-bubble-left-right" :href="route('dashboard.admin.complaints')" :current="request()->routeIs('dashboard.admin.complaints*')" wire:navigate>{{ __('Pengaduan Warga') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="map-pin" :href="route('dashboard.admin.routes')" :current="request()->routeIs('dashboard.admin.routes*')" wire:navigate>{{ __('Rute & Petugas') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('dashboard.admin.reports')" :current="request()->routeIs('dashboard.admin.reports*')" wire:navigate>{{ __('Laporan Lingkungan') }}</flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Pengguna & Entitas')" expandable :expanded="request()->routeIs('dashboard.admin.users*', 'dashboard.admin.entities*')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('dashboard.admin.users')" :current="request()->routeIs('dashboard.admin.users*')" wire:navigate>{{ __('Akun & Hak Akses') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="building-office-2" :href="route('dashboard.admin.entities')" :current="request()->routeIs('dashboard.admin.entities*')" wire:navigate>{{ __('Validasi Entitas') }}</flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Keuangan & Sistem')" expandable :expanded="request()->routeIs('dashboard.admin.points*', 'dashboard.admin.retributions*', 'dashboard.admin.master-data*', 'dashboard.admin.broadcasts*', 'dashboard.admin.activity-logs*')" class="grid">
                        <flux:sidebar.item icon="receipt-percent" :href="route('dashboard.admin.retributions')" :current="request()->routeIs('dashboard.admin.retributions*')" wire:navigate>{{ __('Retribusi Sampah') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('dashboard.admin.points')" :current="request()->routeIs('dashboard.admin.points*')" wire:navigate>{{ __('Poin & Pencairan') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="circle-stack" :href="route('dashboard.admin.master-data')" :current="request()->routeIs('dashboard.admin.master-data*')" wire:navigate>{{ __('Data Master') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="megaphone" :href="route('dashboard.admin.broadcasts')" :current="request()->routeIs('dashboard.admin.broadcasts*')" wire:navigate>{{ __('Broadcast') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('dashboard.admin.activity-logs')" :current="request()->routeIs('dashboard.admin.activity-logs*')" wire:navigate>{{ __('Log Aktivitas') }}</flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif (auth()->user()->isPengelolaTps3r())
                    <flux:sidebar.item icon="home" :href="route('dashboard.tps3r')" :current="request()->routeIs('dashboard.tps3r', 'dashboard')" wire:navigate>{{ __('Dashboard TPS3R') }}</flux:sidebar.item>

                    <flux:sidebar.group :heading="__('Operasional Sampah')" expandable :expanded="request()->routeIs('dashboard.tps3r.deposits*', 'dashboard.tps3r.volume-recap', 'dashboard.tps3r.processing.*')" class="grid">
                        <flux:sidebar.item icon="archive-box-arrow-down" :href="route('dashboard.tps3r.deposits')" :current="request()->routeIs('dashboard.tps3r.deposits*')" wire:navigate>{{ __('Setoran Masuk') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="chart-bar" :href="route('dashboard.tps3r.volume-recap')" :current="request()->routeIs('dashboard.tps3r.volume-recap')" wire:navigate>{{ __('Rekap Volume') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-path" :href="route('dashboard.tps3r.processing.index')" :current="request()->routeIs('dashboard.tps3r.processing.*')" wire:navigate>{{ __('Pengolahan Sampah') }}</flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Marketplace')" expandable :expanded="request()->routeIs('dashboard.tps3r.products*', 'dashboard.tps3r.orders*', 'dashboard.tps3r.withdrawals.*')" class="grid">
                        <flux:sidebar.item icon="shopping-bag" :href="route('dashboard.tps3r.products')" :current="request()->routeIs('dashboard.tps3r.products*')" wire:navigate>{{ __('Katalog Produk') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('dashboard.tps3r.orders')" :current="request()->routeIs('dashboard.tps3r.orders*')" wire:navigate>{{ __('Pesanan Masuk') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('dashboard.tps3r.withdrawals.index')" :current="request()->routeIs('dashboard.tps3r.withdrawals.*')" wire:navigate>{{ __('Pendapatan Toko') }}</flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Pengaturan')" expandable :expanded="request()->routeIs('dashboard.tps3r.profile.*', 'dashboard.tps3r.payment-settings*')" class="grid">
                        <flux:sidebar.item icon="cog-6-tooth" :href="route('dashboard.tps3r.profile.edit')" :current="request()->routeIs('dashboard.tps3r.profile.*')" wire:navigate>{{ __('Profil & Kapasitas') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('dashboard.tps3r.payment-settings')" :current="request()->routeIs('dashboard.tps3r.payment-settings*')" wire:navigate>{{ __('Pembayaran Midtrans') }}</flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif (auth()->user()->isPemrakarsa())
                    <flux:sidebar.item icon="home" :href="route('dashboard.pemrakarsa')" :current="request()->routeIs('dashboard.pemrakarsa', 'dashboard')" wire:navigate>{{ __('Ringkasan') }}</flux:sidebar.item>

                    <flux:sidebar.group :heading="__('Pelaporan Lingkungan')" expandable :expanded="request()->routeIs('dashboard.pemrakarsa.reports*')" class="grid">
                        <flux:sidebar.item icon="document-text" :href="route('dashboard.pemrakarsa.reports')" :current="request()->routeIs('dashboard.pemrakarsa.reports')" wire:navigate>{{ __('Riwayat Laporan') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-up-tray" :href="route('dashboard.pemrakarsa.reports.create')" :current="request()->routeIs('dashboard.pemrakarsa.reports.create')" wire:navigate>{{ __('Unggah Laporan') }}</flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.item icon="building-office-2" :href="route('dashboard.pemrakarsa.profile.edit')" :current="request()->routeIs('dashboard.pemrakarsa.profile.*')" wire:navigate>{{ __('Profil & Legalitas') }}</flux:sidebar.item>
                @else
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            <flux:sidebar.nav class="civic-sidebar-secondary-nav mt-3 border-t border-civic-100/80 pt-3 dark:border-civic-900/70">
                <flux:sidebar.item icon="globe-alt" :href="route('home')" target="_blank">{{ __('Portal Publik') }}</flux:sidebar.item>
                @if (auth()->user()->isAdminDlh())
                    <flux:sidebar.item icon="credit-card" :href="route('dashboard.tps3r.payment-settings')" :current="request()->routeIs('dashboard.tps3r.payment-settings*')" wire:navigate>{{ __('Setup Midtrans') }}</flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur lg:hidden dark:border-slate-800 dark:bg-slate-950/95">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <div class="ms-2 flex min-w-0 items-center gap-2">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white p-1 ring-1 ring-civic-200 dark:bg-slate-900 dark:ring-civic-800">
                    <x-app-logo-icon class="size-6" />
                </span>
                <div class="min-w-0">
                    <p class="truncate font-display text-xs font-extrabold text-slate-950 dark:text-white">Madiun Terpadu</p>
                    <p class="truncate text-[0.58rem] font-bold uppercase tracking-wider text-civic-700 dark:text-civic-300">{{ auth()->user()->role->label() }}</p>
                </div>
            </div>

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                <flux:menu>
                    <div class="flex items-center gap-3 px-2 py-2">
                        <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                        <div class="min-w-0">
                            <flux:heading class="truncate font-display text-sm font-bold">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>

                    <flux:menu.separator />

                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Pengaturan akun') }}</flux:menu.item>
                    <flux:menu.item x-data icon="moon" x-show="$flux.appearance !== 'dark'" x-on:click="$flux.appearance = 'dark'" class="cursor-pointer">{{ __('Mode gelap') }}</flux:menu.item>
                    <flux:menu.item x-data icon="sun" x-show="$flux.appearance === 'dark'" x-on:click="$flux.appearance = 'light'" class="cursor-pointer">{{ __('Mode terang') }}</flux:menu.item>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="logout-button">{{ __('Keluar') }}</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
