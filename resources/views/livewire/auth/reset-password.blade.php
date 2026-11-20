<x-layouts::auth :title="__('Atur Ulang Kata Sandi')">
    @php
        $mobileResetUrl = config('services.mobile_app.password_reset_url', 'madiunterpadu://auth/reset-password').'?'.http_build_query([
            'token' => request()->route('token'),
            'email' => request('email'),
        ]);
    @endphp

    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Buat Kata Sandi Baru')"
            :description="__('Masukkan kata sandi baru untuk menyelesaikan pemulihan akun melalui tautan email')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <a
            href="{{ $mobileResetUrl }}"
            class="inline-flex w-full items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200"
            data-test="open-password-reset-in-app"
        >
            {{ __('Buka di Aplikasi Android') }}
        </a>

        <div class="flex items-center gap-3 text-xs font-medium uppercase tracking-wide text-zinc-400" aria-hidden="true">
            <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></span>
            <span>{{ __('atau lanjutkan di web') }}</span>
            <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></span>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Alamat Email')"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Kata Sandi Baru')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Kata sandi baru')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Konfirmasi Kata Sandi Baru')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Ulangi kata sandi baru')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Simpan Kata Sandi Baru') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
