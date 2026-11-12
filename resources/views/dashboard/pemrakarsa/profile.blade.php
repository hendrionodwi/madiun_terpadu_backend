<x-layouts::app :title="__('Profil Perusahaan')">
    @php
        $profileData = \App\Support\CivicProfilePresenter::forUser(auth()->user(), 'entity', $verification);
        $labelClass = 'block text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-slate-600 dark:text-slate-300';
        $inputClass = 'mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 shadow-xs outline-none transition placeholder:text-slate-400 focus:border-civic-500 focus:bg-white focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950/70 dark:text-slate-100 dark:focus:border-civic-500 dark:focus:bg-slate-950 dark:focus:ring-civic-900';
        $initialSubTab = $errors->hasAny(['nib', 'legal_document']) ? 'legalitas' : 'perusahaan';
    @endphp

    <div class="civic-page mx-auto w-full max-w-6xl">
        <x-civic.profile-cover :profile-data="$profileData" />

        <section
            x-data="{ subTab: @js($initialSubTab) }"
            class="civic-card overflow-hidden"
        >
            <x-civic.profile-tabs :tabs="$profileData['tabs']" />

            <div class="p-5 sm:p-7">
                <div class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                    <div>
                        <h1 class="font-display text-lg font-extrabold tracking-tight text-slate-950 dark:text-white">
                            Profil Perusahaan & Legalitas
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Lengkapi identitas instansi sebelum mengirim laporan lingkungan semester ke DLH Kota Madiun.
                        </p>
                    </div>

                    <div class="inline-flex rounded-xl border border-slate-200 bg-slate-100/80 p-1 dark:border-slate-800 dark:bg-slate-950">
                        <button
                            type="button"
                            @click="subTab = 'perusahaan'"
                            :class="subTab === 'perusahaan'
                                ? 'bg-white text-civic-700 shadow-xs dark:bg-slate-800 dark:text-civic-300'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                            class="min-h-10 rounded-lg px-3.5 py-2 text-xs font-bold transition"
                        >
                            Identitas Perusahaan
                        </button>
                        <button
                            type="button"
                            @click="subTab = 'legalitas'"
                            :class="subTab === 'legalitas'
                                ? 'bg-white text-civic-700 shadow-xs dark:bg-slate-800 dark:text-civic-300'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                            class="min-h-10 rounded-lg px-3.5 py-2 text-xs font-bold transition"
                        >
                            Dokumen Legalitas
                        </button>
                    </div>
                </div>

                @if (session('status'))
                    <div class="civic-alert-success mb-6" role="status">
                        <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="civic-alert-danger mb-6" role="alert">
                        <ul class="list-disc space-y-1 ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.pemrakarsa.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div x-show="subTab === 'perusahaan'" class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="pemrakarsa_nama" class="{{ $labelClass }}">
                                Nama Instansi / Badan Usaha <span class="text-rose-500">*</span>
                            </label>
                            <input id="pemrakarsa_nama" name="nama_instansi" value="{{ old('nama_instansi', $profile->nama_instansi) }}" required class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="pemrakarsa_jenis" class="{{ $labelClass }}">Jenis Usaha / Instansi</label>
                            <input id="pemrakarsa_jenis" name="jenis_instansi" value="{{ old('jenis_instansi', $profile->jenis_instansi) }}" placeholder="Rumah Sakit / Industri / Pusat Perbelanjaan" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="pemrakarsa_pic" class="{{ $labelClass }}">Nama Penanggung Jawab</label>
                            <input id="pemrakarsa_pic" name="penanggung_jawab" value="{{ old('penanggung_jawab', $profile->penanggung_jawab) }}" placeholder="Nama lengkap penanggung jawab" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="pemrakarsa_lat" class="{{ $labelClass }}">Koordinat Latitude</label>
                            <input id="pemrakarsa_lat" type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $profile->latitude) }}" placeholder="-7.6288" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="pemrakarsa_lng" class="{{ $labelClass }}">Koordinat Longitude</label>
                            <input id="pemrakarsa_lng" type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $profile->longitude) }}" placeholder="111.5240" class="{{ $inputClass }}">
                        </div>

                        <div class="md:col-span-2">
                            <label for="pemrakarsa_alamat" class="{{ $labelClass }}">Alamat Instansi Lengkap</label>
                            <textarea id="pemrakarsa_alamat" name="alamat_instansi" rows="3" placeholder="Tuliskan alamat domisili usaha/instansi..." class="{{ $inputClass }}">{{ old('alamat_instansi', $profile->alamat_instansi) }}</textarea>
                        </div>
                    </div>

                    <div x-show="subTab === 'legalitas'" x-cloak class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,1fr)]">
                        <div class="grid content-start gap-5">
                            <div class="rounded-2xl border border-civic-200/80 bg-civic-50/70 p-4 dark:border-civic-900 dark:bg-civic-950/50">
                                <p class="font-display text-sm font-extrabold text-slate-900 dark:text-white">Validasi Legalitas Usaha &amp; NIB</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                                    Isi Nomor Induk Berusaha (NIB) dan unggah dokumen legalitas resmi (PDF/JPG/PNG, maks. 5 MB) agar Admin DLH dapat menyetujui akun pelaporan Anda.
                                </p>
                            </div>

                            <div>
                                <label for="pemrakarsa_nib" class="{{ $labelClass }}">Nomor Induk Berusaha (NIB)</label>
                                <input id="pemrakarsa_nib" name="nib" value="{{ old('nib', $profile->nib) }}" placeholder="Masukkan 13 digit NIB" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="pemrakarsa_legal_file" class="{{ $labelClass }}">Unggah Berkas Legalitas (PDF / JPG / PNG)</label>
                                <div class="mt-1.5 rounded-xl border border-slate-200 bg-slate-50/60 p-3 dark:border-slate-700 dark:bg-slate-950/70">
                                    <input
                                        id="pemrakarsa_legal_file"
                                        type="file"
                                        name="legal_document"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        class="block w-full text-xs text-slate-700 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-civic-100 file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-civic-800 hover:file:bg-civic-200 dark:text-slate-300 dark:file:bg-civic-900 dark:file:text-civic-200"
                                    >
                                </div>
                            </div>
                        </div>

                        <x-civic.entity-verification-panel :verification="$verification" number-label="NIB Diverifikasi" note-label="Catatan DLH" />
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
                        <a href="{{ route('dashboard.pemrakarsa') }}" wire:navigate class="civic-button-secondary">
                            <flux:icon.document-text class="size-4" />
                            Ke laporan semester
                        </a>

                        <button type="submit" class="civic-button-primary min-h-11 px-6">
                            Simpan profil &amp; ajukan validasi
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-layouts::app>
