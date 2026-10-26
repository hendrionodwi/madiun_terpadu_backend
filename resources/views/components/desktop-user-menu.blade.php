@props([
    'name' => null,
])

<flux:dropdown position="bottom" align="start" {{ $attributes->class('rounded-2xl border border-slate-200 bg-slate-50/80 p-1.5 dark:border-slate-800 dark:bg-slate-900/70') }}>
    <flux:sidebar.profile
        :name="$name ?? auth()->user()->name"
        :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
    />

    <flux:menu>
        <div class="flex items-center gap-3 px-2 py-2 text-start">
            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
            <div class="grid min-w-0 flex-1 leading-tight">
                <flux:heading class="truncate font-display text-sm font-bold">{{ auth()->user()->name }}</flux:heading>
                <span class="mt-1 w-fit rounded-full bg-civic-100 px-2 py-0.5 text-[0.62rem] font-extrabold uppercase tracking-wider text-civic-800 dark:bg-civic-900 dark:text-civic-200">
                    {{ auth()->user()->role->label() }}
                </span>
                <flux:text class="mt-1 truncate text-xs text-slate-500">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Pengaturan akun') }}
            </flux:menu.item>
            <flux:menu.item x-data icon="moon" x-show="$flux.appearance !== 'dark'" x-on:click="$flux.appearance = 'dark'" class="cursor-pointer">
                {{ __('Mode gelap') }}
            </flux:menu.item>
            <flux:menu.item x-data icon="sun" x-show="$flux.appearance === 'dark'" x-on:click="$flux.appearance = 'light'" class="cursor-pointer">
                {{ __('Mode terang') }}
            </flux:menu.item>
        </flux:menu.radio.group>

        <flux:menu.separator />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="logout-button">
                {{ __('Keluar') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
