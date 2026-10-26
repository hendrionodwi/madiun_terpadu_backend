<x-layouts::app :title="__('Kelola Pengguna & Hak Akses (RBAC) - Admin DLH')">
    @php
        $roleFilters = [
            '' => ['label' => 'Semua', 'count' => $roleCounts['all']],
            'warga' => ['label' => 'Warga', 'count' => $roleCounts['warga']],
            'petugas_lapangan' => ['label' => 'Petugas', 'count' => $roleCounts['petugas_lapangan']],
            'pengelola_tps3r' => ['label' => 'TPS3R', 'count' => $roleCounts['pengelola_tps3r']],
            'pemrakarsa' => ['label' => 'Pemrakarsa', 'count' => $roleCounts['pemrakarsa']],
            'admin_dlh' => ['label' => 'Admin DLH', 'count' => $roleCounts['admin_dlh']],
        ];

        $modalToReopen = old('_form');
    @endphp

    <div
        class="civic-page"
        @if ($errors->any() && is_string($modalToReopen) && ($modalToReopen === 'create-user' || str_starts_with($modalToReopen, 'edit-user-')))
            x-data
            x-init="$nextTick(() => $flux.modal(@js($modalToReopen)).show())"
        @endif
    >
        <section class="relative isolate overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 p-5 shadow-[0_22px_55px_-38px_rgba(15,23,42,0.46)] backdrop-blur-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900/95">
            <span class="pointer-events-none absolute -top-20 -right-16 -z-10 size-64 rounded-full bg-civic-100/70 blur-3xl dark:bg-civic-800/10" aria-hidden="true"></span>

            <nav class="mb-4 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.admin') }}" class="font-semibold transition hover:text-civic-700 dark:hover:text-civic-300" wire:navigate>Dashboard Admin</a>
                <flux:icon icon="chevron-right" class="size-3.5" />
                <span class="font-semibold text-slate-700 dark:text-slate-200">Akun & Hak Akses</span>
            </nav>

            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="civic-icon-tile size-12 rounded-2xl">
                        <flux:icon icon="users" class="size-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.16em] text-civic-700 dark:text-civic-300">Administrasi pengguna</p>
                        <h1 class="mt-1 font-display text-2xl font-extrabold tracking-[-0.035em] text-slate-950 sm:text-3xl dark:text-white">Manajemen Pengguna & Hak Akses</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">Kelola akun, peran, dan status akses seluruh pengguna layanan lingkungan Kota Madiun.</p>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <div class="rounded-xl border border-civic-100 bg-civic-50/80 px-3.5 py-2 text-right dark:border-civic-900 dark:bg-civic-950/40">
                        <p class="text-[0.58rem] font-extrabold uppercase tracking-[0.12em] text-civic-600 dark:text-civic-400">Total akun</p>
                        <p class="mt-0.5 font-display text-lg font-extrabold text-civic-950 dark:text-civic-100">{{ number_format($roleCounts['all']) }}</p>
                    </div>

                    <flux:modal.trigger name="create-user">
                        <flux:button variant="primary" icon="user-plus">Tambah akun</flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon icon="check-circle" class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <div class="flex items-start gap-3">
                    <flux:icon icon="exclamation-triangle" class="mt-0.5 size-5 shrink-0" />
                    <div>
                        <p class="font-bold">Data belum dapat disimpan.</p>
                        <p class="mt-1 text-xs opacity-80">Periksa kembali kolom yang ditandai pada formulir.</p>
                    </div>
                </div>
            </div>
        @endif

        <section class="civic-card p-4 sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="civic-filter-rail" aria-label="Filter berdasarkan role">
                    @foreach ($roleFilters as $roleValue => $filter)
                        @php($isActiveRole = ($currentRole ?? '') === $roleValue)
                        <a
                            href="{{ route('dashboard.admin.users', array_filter(['role' => $roleValue, 'q' => $search])) }}"
                            class="civic-filter-chip {{ $isActiveRole ? 'civic-filter-chip-active' : '' }}"
                            @if ($isActiveRole) aria-current="page" @endif
                        >
                            {{ $filter['label'] }}
                            <span class="ms-1 rounded-md bg-slate-100 px-1.5 py-0.5 text-[0.6rem] text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $filter['count'] }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('dashboard.admin.users') }}" class="flex w-full flex-col gap-2 sm:flex-row xl:max-w-2xl">
                    @if ($currentRole)
                        <input type="hidden" name="role" value="{{ $currentRole }}">
                    @endif

                    <flux:input name="q" :value="$search" icon="magnifying-glass" placeholder="Cari nama, email, atau nomor telepon..." class="min-w-0 flex-1" />
                    <div class="flex gap-2">
                        <flux:button type="submit" variant="primary">Cari</flux:button>
                        @if ($search || $currentRole)
                            <flux:button :href="route('dashboard.admin.users')" variant="ghost" wire:navigate>Reset</flux:button>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        <section class="civic-card overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <div>
                    <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Daftar pengguna</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} akun.</p>
                </div>
                @if ($search || $currentRole)
                    <span class="civic-status-info">Filter aktif</span>
                @endif
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="w-full min-w-[64rem] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50/80 text-[0.64rem] font-extrabold uppercase tracking-[0.1em] text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-3.5">Pengguna</th>
                            <th class="px-4 py-3.5">Peran</th>
                            <th class="px-4 py-3.5">Identitas role</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Terdaftar</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($users as $user)
                            <tr class="group transition hover:bg-civic-50/45 dark:hover:bg-civic-950/15">
                                <td class="px-5 py-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-civic-100 to-civic-200 font-display text-xs font-extrabold text-civic-800 ring-1 ring-civic-200 dark:from-civic-950 dark:to-civic-900 dark:text-civic-200 dark:ring-civic-800">{{ $user->initials() }}</span>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="truncate font-bold text-slate-950 dark:text-white">{{ $user->name }}</p>
                                                @if (auth()->id() === $user->id)
                                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[0.58rem] font-extrabold uppercase tracking-wider text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Anda</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 truncate text-[0.7rem] text-slate-500 dark:text-slate-400">{{ $user->email }} <span aria-hidden="true">•</span> {{ $user->phone_number ?: 'Tanpa nomor telepon' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4"><x-civic.role-badge :role="$user->role" /></td>
                                <td class="max-w-64 px-4 py-4"><x-civic.user-profile-summary :user="$user" /></td>
                                <td class="px-4 py-4">
                                    <span class="{{ $user->is_active ? 'civic-status-success' : 'civic-status-danger' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $user->created_at->translatedFormat('d M Y') }}</p>
                                    <p class="mt-1 text-[0.65rem] text-slate-400">{{ $user->created_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" icon="ellipsis-horizontal" square aria-label="Aksi untuk {{ $user->name }}" />
                                        <flux:menu>
                                            <flux:menu.item x-data icon="pencil-square" x-on:click="$flux.modal('edit-user-{{ $user->id }}').show()">Ubah akun</flux:menu.item>
                                            @if (auth()->id() !== $user->id)
                                                <flux:menu.separator />
                                                <flux:menu.item x-data :icon="$user->is_active ? 'no-symbol' : 'check-circle'" :variant="$user->is_active ? 'danger' : 'default'" x-on:click="$flux.modal('status-user-{{ $user->id }}').show()">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </flux:menu.item>
                                            @else
                                                <flux:menu.item icon="shield-check" disabled>Akun yang digunakan</flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14">
                                    <div class="mx-auto max-w-sm text-center">
                                        <span class="mx-auto flex size-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"><flux:icon icon="users" class="size-5" /></span>
                                        <p class="mt-3 font-bold text-slate-700 dark:text-slate-200">Pengguna tidak ditemukan</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Ubah kata pencarian atau reset filter untuk melihat akun lainnya.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 lg:hidden dark:divide-slate-800">
                @forelse ($users as $user)
                    <article class="p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-civic-100 font-display text-xs font-extrabold text-civic-800 dark:bg-civic-950 dark:text-civic-200">{{ $user->initials() }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold text-slate-950 dark:text-white">{{ $user->name }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                            </div>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" icon="ellipsis-horizontal" square aria-label="Aksi untuk {{ $user->name }}" />
                                <flux:menu>
                                    <flux:menu.item x-data icon="pencil-square" x-on:click="$flux.modal('edit-user-{{ $user->id }}').show()">Ubah akun</flux:menu.item>
                                    @if (auth()->id() !== $user->id)
                                        <flux:menu.separator />
                                        <flux:menu.item x-data :icon="$user->is_active ? 'no-symbol' : 'check-circle'" :variant="$user->is_active ? 'danger' : 'default'" x-on:click="$flux.modal('status-user-{{ $user->id }}').show()">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <x-civic.role-badge :role="$user->role" />
                            <span class="{{ $user->is_active ? 'civic-status-success' : 'civic-status-danger' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>

                        <div class="mt-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                            <x-civic.user-profile-summary :user="$user" />
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 text-[0.68rem] text-slate-500 dark:text-slate-400">
                            <span>{{ $user->phone_number ?: 'Nomor telepon belum diisi' }}</span>
                            <span class="shrink-0">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">Pengguna tidak ditemukan.</div>
                @endforelse
            </div>

            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-4 py-4 dark:border-slate-800">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>

    <flux:modal name="create-user" flyout variant="floating" scroll="body" class="md:w-[32rem]">
        <form method="POST" action="{{ route('dashboard.admin.users.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_form" value="create-user">

            <div class="flex items-start gap-3">
                <span class="civic-icon-tile"><flux:icon icon="user-plus" class="size-5" /></span>
                <div>
                    <flux:heading size="lg">Tambah akun baru</flux:heading>
                    <flux:text class="mt-1">Buat identitas login dan tentukan hak akses awal pengguna.</flux:text>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:input name="name" label="Nama lengkap" :value="old('_form') === 'create-user' ? old('name') : ''" autocomplete="name" required />
                    <flux:error name="name" />
                </div>
                <div class="sm:col-span-2">
                    <flux:input type="email" name="email" label="Alamat email" :value="old('_form') === 'create-user' ? old('email') : ''" autocomplete="email" required />
                    <flux:error name="email" />
                </div>
                <div>
                    <flux:input name="phone_number" label="Nomor telepon" :value="old('_form') === 'create-user' ? old('phone_number') : ''" autocomplete="tel" placeholder="08xxxxxxxxxx" />
                    <flux:error name="phone_number" />
                </div>
                <div>
                    <flux:select name="role" label="Peran pengguna" required>
                        @foreach (\App\Enums\UserRole::cases() as $role)
                            <flux:select.option :value="$role->value" :selected="old('_form') === 'create-user' && old('role') === $role->value">{{ $role->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="role" />
                </div>
                <div class="sm:col-span-2">
                    <flux:input type="password" name="password" label="Password awal" autocomplete="new-password" minlength="8" viewable required />
                    <flux:text class="mt-1.5 text-xs">Minimal 8 karakter. Pengguna dapat menggantinya melalui pengaturan akun.</flux:text>
                    <flux:error name="password" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
                <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="user-plus">Buat akun</flux:button>
            </div>
        </form>
    </flux:modal>

    @foreach ($users as $user)
        @php($editFormName = 'edit-user-'.$user->id)

        <flux:modal :name="$editFormName" flyout variant="floating" scroll="body" class="md:w-[30rem]">
            <form method="POST" action="{{ route('dashboard.admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_form" value="{{ $editFormName }}">

                <div class="flex items-center gap-3">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-civic-100 font-display text-xs font-extrabold text-civic-800 dark:bg-civic-950 dark:text-civic-200">{{ $user->initials() }}</span>
                    <div class="min-w-0">
                        <flux:heading size="lg">Ubah akun pengguna</flux:heading>
                        <flux:text class="mt-1 truncate">{{ $user->email }}</flux:text>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <flux:input name="name" label="Nama lengkap" :value="$modalToReopen === $editFormName ? old('name', $user->name) : $user->name" required />
                        @if ($modalToReopen === $editFormName)<flux:error name="name" />@endif
                    </div>

                    @if (auth()->id() === $user->id)
                        <input type="hidden" name="role" value="{{ $user->role->value }}">
                        <input type="hidden" name="is_active" value="1">
                        <flux:select label="Peran pengguna" disabled>
                            <flux:select.option selected>{{ $user->role->label() }}</flux:select.option>
                        </flux:select>
                        <flux:callout icon="shield-check" color="amber" heading="Akun yang sedang digunakan" text="Role dan status akun ini dikunci untuk mencegah admin kehilangan akses." />
                    @else
                        <div>
                            <flux:select name="role" label="Peran pengguna" required>
                                @foreach (\App\Enums\UserRole::cases() as $role)
                                    <flux:select.option :value="$role->value" :selected="($modalToReopen === $editFormName ? old('role', $user->role->value) : $user->role->value) === $role->value">{{ $role->label() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @if ($modalToReopen === $editFormName)<flux:error name="role" />@endif
                        </div>
                        <div>
                            <flux:select name="is_active" label="Status akun" required>
                                <flux:select.option value="1" :selected="(string) ($modalToReopen === $editFormName ? old('is_active', (int) $user->is_active) : (int) $user->is_active) === '1'">Aktif</flux:select.option>
                                <flux:select.option value="0" :selected="(string) ($modalToReopen === $editFormName ? old('is_active', (int) $user->is_active) : (int) $user->is_active) === '0'">Nonaktif</flux:select.option>
                            </flux:select>
                            @if ($modalToReopen === $editFormName)<flux:error name="is_active" />@endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
                    <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary" icon="check">Simpan perubahan</flux:button>
                </div>
            </form>
        </flux:modal>

        @if (auth()->id() !== $user->id)
            <flux:modal :name="'status-user-'.$user->id" class="min-w-[22rem]">
                <form method="POST" action="{{ route('dashboard.admin.users.toggle', $user) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $user->is_active ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                            <flux:icon :icon="$user->is_active ? 'no-symbol' : 'check-circle'" class="size-5" />
                        </span>
                        <div>
                            <flux:heading size="lg">{{ $user->is_active ? 'Nonaktifkan akun?' : 'Aktifkan kembali akun?' }}</flux:heading>
                            <flux:text class="mt-2">{{ $user->name }} {{ $user->is_active ? 'tidak akan dapat masuk sampai akunnya diaktifkan kembali.' : 'akan kembali memperoleh akses sesuai perannya.' }}</flux:text>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                        <flux:button type="submit" :variant="$user->is_active ? 'danger' : 'primary'">{{ $user->is_active ? 'Ya, nonaktifkan' : 'Aktifkan akun' }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
    @endforeach
</x-layouts::app>
