<x-layouts::auth :title="__('Daftar Akun Warga')">
    <div class="flex flex-col gap-6">
        <x-auth-header 
            :title="__('Pendaftaran Warga Baru')" 
            :description="__('Daftar untuk mengakses bank sampah digital, jadwal penjemputan, dan kumpulkan poin reward')" 
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <!-- Info Note -->
        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/70 p-3 text-xs text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200 flex items-start gap-2.5">
            <flux:icon.light-bulb class="size-5 shrink-0" />
            <div>
                <strong class="font-semibold">Informasi Role:</strong> Formulir ini khusus untuk pendaftaran masyarakat/warga. Untuk akun resmi Petugas Lapangan, Pengelola TPS3R, atau Pemrakarsa, akun didaftarkan melalui Dinas Lingkungan Hidup.
            </div>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Nama Lengkap (Sesuai KTP)')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Nama lengkap')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Alamat Email Aktif')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="nama@email.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Kata Sandi')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Minimal 8 karakter')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Konfirmasi Kata Sandi')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Ulangi kata sandi')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end pt-2">
                <flux:button type="submit" variant="primary" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5" data-test="register-user-button">
                    {{ __('Daftar Sekarang') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400 border-t border-zinc-100 dark:border-zinc-800 pt-4">
            <span>{{ __('Sudah memiliki akun?') }}</span>
            <flux:link :href="route('login')" class="text-emerald-700 font-semibold hover:text-emerald-800 dark:text-emerald-400" wire:navigate>{{ __('Masuk di sini') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
