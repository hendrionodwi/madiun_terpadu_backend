<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Pengaturan tampilan') }}</flux:heading>

    <x-settings.layout
        active-tab="appearance"
        :heading="__('Tampilan Aplikasi')"
        :subheading="__('Pilih mode tampilan terang, gelap, atau mengikuti pengaturan perangkat Anda')"
    >
        <div class="max-w-xl space-y-4">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Terang') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Gelap') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('Sistem') }}</flux:radio>
            </flux:radio.group>

            <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">
                {{ __('Perubahan tema langsung diterapkan pada seluruh halaman dashboard tanpa memengaruhi sesi login Anda.') }}
            </p>
        </div>
    </x-settings.layout>
</section>
