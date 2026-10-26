<x-layouts::auth :title="__('Lupa Kata Sandi')">
    <div class="flex flex-col gap-6">
        <x-auth-header 
            :title="__('Lupa Kata Sandi?')" 
            :description="__('Masukkan alamat email yang terdaftar untuk menerima tautan pemulihan kata sandi')" 
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Alamat Email')"
                type="email"
                required
                autofocus
                placeholder="nama@email.com"
            />

            <flux:button variant="primary" type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5" data-test="email-password-reset-link-button">
                {{ __('Kirim Tautan Pemulihan') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400 border-t border-zinc-100 dark:border-zinc-800 pt-4">
            <span>{{ __('Ingat kata sandi Anda?') }}</span>
            <flux:link :href="route('login')" class="text-emerald-700 font-semibold hover:text-emerald-800 dark:text-emerald-400" wire:navigate>{{ __('Kembali ke login') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
