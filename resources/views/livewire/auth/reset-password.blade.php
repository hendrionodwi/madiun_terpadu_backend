<x-layouts::auth :title="__('Atur Ulang Kata Sandi')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Buat Kata Sandi Baru')"
            :description="__('Masukkan kata sandi baru untuk menyelesaikan pemulihan akun melalui tautan email')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

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
