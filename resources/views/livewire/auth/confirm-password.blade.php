<x-layouts::auth :title="__('Konfirmasi Kata Sandi')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Konfirmasi Kata Sandi')"
            :description="__('Halaman keamanan memerlukan verifikasi ulang. Masukkan kata sandi akun Anda untuk melanjutkan.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />


        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Kata Sandi')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Kata sandi akun')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Konfirmasi dan Lanjutkan') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
