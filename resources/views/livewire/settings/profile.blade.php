@php
    $user = auth()->user();
    $labelClass = 'block text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-slate-600 dark:text-slate-300';
    $inputClass = 'mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 shadow-xs outline-none transition placeholder:text-slate-400 focus:border-civic-500 focus:bg-white focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950/70 dark:text-slate-100 dark:focus:border-civic-500 dark:focus:bg-slate-950 dark:focus:ring-civic-900';
    $avatarPreviewUrl = null;
    if ($avatar instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
        try {
            $avatarPreviewUrl = $avatar->temporaryUrl();
        } catch (\Throwable) {
            $avatarPreviewUrl = null;
        }
    }
@endphp

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        active-tab="profile"
        :heading="__('Informasi Pribadi')"
        :subheading="__('Perbarui identitas akun, kontak WhatsApp, dan foto profil Anda secara terpusat')"
    >
        @if (session('profile_status'))
            <div class="civic-alert-success mb-6" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('profile_status') }}</span>
            </div>
        @endif

        <form wire:submit="updateProfileInformation" class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="profile_name" class="{{ $labelClass }}">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input
                    id="profile_name"
                    type="text"
                    wire:model="name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Masukkan nama lengkap"
                    class="{{ $inputClass }}"
                >
                @error('name')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile_email" class="{{ $labelClass }}">
                    Alamat Email <span class="text-rose-500">*</span>
                </label>
                <input
                    id="profile_email"
                    type="email"
                    wire:model="email"
                    required
                    autocomplete="email"
                    placeholder="nama@instansi.go.id"
                    class="{{ $inputClass }}"
                >
                @error('email')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="profile_phone" class="{{ $labelClass }}">
                    Nomor WhatsApp / Telepon
                </label>
                <input
                    id="profile_phone"
                    type="text"
                    wire:model="phone_number"
                    autocomplete="tel"
                    placeholder="08xxxxxxxxxx"
                    class="{{ $inputClass }}"
                >
                @error('phone_number')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="{{ $labelClass }}">Peran &amp; Status Akun</span>
                <div class="mt-1.5 flex min-h-11 items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-100/70 px-4 py-2 text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300">
                    <span class="font-semibold">{{ $user->role->label() }}</span>
                    <span class="{{ $user->is_active ? 'civic-status-success' : 'civic-status-warning' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>

            @if ($user->isAdminDlh())
                <div>
                    <label for="profile_nip" class="{{ $labelClass }}">NIP Pegawai</label>
                    <input id="profile_nip" type="text" wire:model="nip" placeholder="Nomor Induk Pegawai" class="{{ $inputClass }}">
                    @error('nip')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile_jabatan" class="{{ $labelClass }}">Jabatan Kedinasan</label>
                    <input id="profile_jabatan" type="text" wire:model="jabatan" placeholder="Contoh: Kepala Bidang Pengelolaan Sampah" class="{{ $inputClass }}">
                    @error('jabatan')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="profile_bidang" class="{{ $labelClass }}">Bidang / Unit Kerja DLH</label>
                    <input id="profile_bidang" type="text" wire:model="bidang" placeholder="Contoh: Bidang Kebersihan & Pengolahan" class="{{ $inputClass }}">
                    @error('bidang')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($user->isPetugasLapangan())
                <div>
                    <label for="profile_kendaraan" class="{{ $labelClass }}">Jenis Kendaraan Armada</label>
                    <input id="profile_kendaraan" type="text" wire:model="jenis_kendaraan" placeholder="Truk Compactor / Gerobak Motor" class="{{ $inputClass }}">
                    @error('jenis_kendaraan')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile_plat" class="{{ $labelClass }}">Nomor Polisi Kendaraan</label>
                    <input id="profile_plat" type="text" wire:model="plat_nomor" placeholder="AE xxxx XX" class="{{ $inputClass }}">
                    @error('plat_nomor')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($user->isWarga())
                <div>
                    <label for="profile_kecamatan" class="{{ $labelClass }}">Kecamatan Domisili</label>
                    <select id="profile_kecamatan" wire:model="kecamatan" class="{{ $inputClass }}">
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach ($this->districts as $district)
                            <option value="{{ $district }}">{{ $district }}</option>
                        @endforeach
                    </select>
                    @error('kecamatan')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile_kelurahan" class="{{ $labelClass }}">Kelurahan / Desa</label>
                    <input id="profile_kelurahan" type="text" wire:model="kelurahan" placeholder="Nama kelurahan" class="{{ $inputClass }}">
                    @error('kelurahan')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile_rt" class="{{ $labelClass }}">RT</label>
                    <input id="profile_rt" type="text" wire:model="rt" placeholder="001" class="{{ $inputClass }}">
                    @error('rt')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile_rw" class="{{ $labelClass }}">RW</label>
                    <input id="profile_rw" type="text" wire:model="rw" placeholder="002" class="{{ $inputClass }}">
                    @error('rw')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Upload Foto Profil Baru --}}
            <div class="md:col-span-2">
                <label for="profile_avatar" class="{{ $labelClass }}">Unggah Foto Profil Baru</label>
                <div class="mt-1.5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-950/70">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                            @if ($avatarPreviewUrl)
                                <img src="{{ $avatarPreviewUrl }}" alt="{{ __('Pratinjau foto baru') }}" class="size-full object-cover">
                            @elseif ($existingAvatarUrl)
                                <img src="{{ $existingAvatarUrl }}" alt="{{ $user->name }}" class="size-full object-cover">
                            @else
                                <span class="font-display text-sm font-extrabold text-civic-700 dark:text-civic-300">{{ $user->initials() }}</span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <input
                                id="profile_avatar"
                                type="file"
                                wire:model="avatar"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="block w-full text-xs text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-civic-100 file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-civic-800 hover:file:bg-civic-200 dark:text-slate-300 dark:file:bg-civic-900 dark:file:text-civic-200"
                            >
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" wire:loading.remove wire:target="avatar">
                                Format JPG, PNG, atau WebP (maks. 2 MB).
                            </p>
                            <p class="mt-1 text-xs font-semibold text-civic-700 dark:text-civic-300" wire:loading wire:target="avatar">
                                Mengunggah pratinjau foto...
                            </p>
                        </div>
                    </div>

                    @if ($existingAvatarUrl || $avatar)
                        <button
                            type="button"
                            wire:click="removeAvatar"
                            class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/60 dark:text-rose-300"
                        >
                            Hapus Foto
                        </button>
                    @endif
                </div>
                @error('avatar')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            @if ($user->isWarga())
                <div class="md:col-span-2">
                    <label for="profile_alamat" class="{{ $labelClass }}">Alamat Domisili Lengkap</label>
                    <textarea
                        id="profile_alamat"
                        rows="3"
                        wire:model="alamat"
                        placeholder="Tuliskan alamat domisili lengkap Anda..."
                        class="{{ $inputClass }}"
                    ></textarea>
                    @error('alamat')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if ($user->isPengelolaTps3r() || $user->isPemrakarsa())
                <div class="flex flex-col gap-3 rounded-2xl border border-civic-200/80 bg-civic-50/70 p-4 sm:flex-row sm:items-center sm:justify-between md:col-span-2 dark:border-civic-900 dark:bg-civic-950/50">
                    <div class="min-w-0">
                        <p class="font-display text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ $user->isPengelolaTps3r() ? 'Kelola Data Fasilitas & Legalitas TPS3R' : 'Kelola Identitas Perusahaan & Dokumen Legalitas' }}
                        </p>
                        <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-300">
                            {{ $user->isPengelolaTps3r() ? 'Atur nama TPS3R, kapasitas harian, koordinat peta, dan unggah NIB pada tab khusus TPS3R.' : 'Atur nama instansi, NIB, penanggung jawab, koordinat lokasi, dan unggah berkas legalitas DLH.' }}
                        </p>
                    </div>
                    <a
                        href="{{ $user->isPengelolaTps3r() ? route('dashboard.tps3r.profile.edit') : route('dashboard.pemrakarsa.profile.edit') }}"
                        wire:navigate
                        class="civic-button-secondary shrink-0"
                    >
                        Buka Tab Entitas &rarr;
                    </a>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5 md:col-span-2 dark:border-slate-800">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="updateProfileInformation,avatar"
                    class="civic-button-primary min-h-11 px-6"
                >
                    <span wire:loading.remove wire:target="updateProfileInformation">{{ __('Simpan Perubahan') }}</span>
                    <span wire:loading wire:target="updateProfileInformation">{{ __('Menyimpan...') }}</span>
                </button>
            </div>
        </form>

        <div class="mt-8 border-t border-slate-200/80 pt-6 dark:border-slate-800">
            <livewire:settings.delete-user-form />
        </div>
    </x-settings.layout>
</section>
