<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Ubah kata sandi') }}</flux:heading>

    <x-settings.layout
        :heading="__('Ubah Kata Sandi')"
        :subheading="__('Perbarui kata sandi akun yang sedang Anda gunakan')"
    >
        <flux:callout
            icon="shield-check"
            color="emerald"
            :heading="__('Anda sedang masuk ke akun')"
            :text="__('Masukkan kata sandi saat ini untuk memverifikasi identitas. Alur ini tidak mengirim tautan pemulihan melalui email.')"
        />

        <form wire:submit="updatePassword" class="mt-6 w-full space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Kata sandi saat ini')"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />

            <flux:input
                wire:model="password"
                :label="__('Kata sandi baru')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <flux:input
                wire:model="password_confirmation"
                :label="__('Konfirmasi kata sandi baru')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button
                    variant="primary"
                    type="submit"
                    data-test="update-password-button"
                    wire:loading.attr="disabled"
                    wire:target="updatePassword"
                >
                    <span wire:loading.remove wire:target="updatePassword">{{ __('Simpan Kata Sandi Baru') }}</span>
                    <span wire:loading wire:target="updatePassword">{{ __('Menyimpan...') }}</span>
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
