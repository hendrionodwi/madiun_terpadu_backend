<x-layouts::auth :title="__('Masuk ke Portal')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Masuk ke Akun')"
            :description="__('Gunakan akun layanan Anda untuk mengakses dashboard Madiun Terpadu.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <div class="rounded-2xl border border-civic-200 bg-civic-50 p-4 dark:border-civic-900 dark:bg-civic-950/35">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.16em] text-civic-800 dark:text-civic-200">Mode pengujian</p>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pilih role untuk mengisi kredensial demo.</p>
                </div>
                <span class="rounded-full bg-white px-2.5 py-1 text-[0.62rem] font-bold text-civic-700 ring-1 ring-civic-200 dark:bg-slate-900 dark:ring-civic-800">DEMO</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs sm:grid-cols-3">
                @foreach ([
                    ['Admin DLH', 'AD', 'admin.dlh@madiunkota.go.id'],
                    ['TPS3R', 'TP', 'pengelola.tps3r@madiunkota.go.id'],
                    ['Pemrakarsa', 'PM', 'pemrakarsa@madiunkota.go.id'],
                    ['Petugas', 'PL', 'petugas.lapangan@madiunkota.go.id'],
                    ['Warga', 'WR', 'warga@madiunkota.go.id'],
                ] as [$roleLabel, $roleInitial, $roleEmail])
                    <button
                        type="button"
                        onclick="fillDemoRole('{{ $roleEmail }}')"
                        class="group flex items-center gap-2 rounded-xl border border-civic-200 bg-white px-2.5 py-2 text-left font-bold text-civic-950 shadow-sm hover:-translate-y-0.5 hover:border-civic-400 hover:bg-civic-100 dark:border-slate-700 dark:bg-slate-900 dark:text-civic-100 dark:hover:border-civic-700"
                    >
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-civic-100 text-[0.6rem] text-civic-800 group-hover:bg-civic-700 group-hover:text-white dark:bg-civic-900 dark:text-civic-200">{{ $roleInitial }}</span>
                        <span class="truncate">{{ $roleLabel }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <script>
            function fillDemoRole(email) {
                const emailInput = document.querySelector('input[name="email"]');
                const passwordInput = document.querySelector('input[name="password"]');

                if (emailInput) {
                    emailInput.value = email;
                    emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (passwordInput) {
                    passwordInput.value = 'password';
                    passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        </script>

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Alamat Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nama@madiunkota.go.id"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Kata Sandi')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Masukkan kata sandi')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 end-0 text-xs font-semibold text-civic-700 hover:text-civic-900 dark:text-civic-300" :href="route('password.request')" wire:navigate>
                        {{ __('Lupa kata sandi?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Ingat sesi saya di perangkat ini')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full font-bold" data-test="login-button">
                {{ __('Masuk ke Portal Terpadu') }}
            </flux:button>
        </form>

        <div class="border-t border-slate-100 pt-4 text-center text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400">
            <span>{{ __('Belum memiliki akun warga?') }}</span>
            <flux:link :href="route('register')" class="font-bold text-civic-700 hover:text-civic-900 dark:text-civic-300" wire:navigate>{{ __('Daftar Sekarang') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
