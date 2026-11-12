<section class="rounded-2xl border border-rose-200/80 bg-rose-50/50 p-5 dark:border-rose-900/70 dark:bg-rose-950/20">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h3 class="font-display text-sm font-extrabold text-rose-900 dark:text-rose-200">
                {{ __('Hapus Akun Permanen') }}
            </h3>
            <p class="mt-1 text-xs leading-5 text-rose-700/90 dark:text-rose-300/80">
                {{ __('Menghapus akun akan menghapus akses Anda dan seluruh data terkait secara permanen.') }}
            </p>
        </div>

        <flux:modal.trigger name="confirm-user-deletion">
            <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
                {{ __('Hapus Akun') }}
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Konfirmasi Penghapusan Akun') }}</flux:heading>

                <flux:subheading>
                    {{ __('Setelah akun dihapus, seluruh data dan akses akan terhapus permanen. Masukkan kata sandi saat ini untuk melanjutkan.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Kata sandi')" type="password" viewable />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Batalkan') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Hapus Akun') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
