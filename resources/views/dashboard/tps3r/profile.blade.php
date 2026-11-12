<x-layouts::app :title="__('Profil TPS3R')">
    @php
        $profileData = \App\Support\CivicProfilePresenter::forUser(auth()->user(), 'entity', $verification);
        $labelClass = 'block text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-slate-600 dark:text-slate-300';
        $inputClass = 'mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-900 shadow-xs outline-none transition placeholder:text-slate-400 focus:border-civic-500 focus:bg-white focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950/70 dark:text-slate-100 dark:focus:border-civic-500 dark:focus:bg-slate-950 dark:focus:ring-civic-900';
        $initialSubTab = $errors->hasAny(['document_number', 'legal_document']) ? 'legalitas' : 'fasilitas';
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
                            Profil Fasilitas & Kapasitas
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Data ini dipakai pada penerimaan setoran, rekap volume, portal publik, dan validasi DLH.
                        </p>
                    </div>

                    <div class="inline-flex rounded-xl border border-slate-200 bg-slate-100/80 p-1 dark:border-slate-800 dark:bg-slate-950">
                        <button
                            type="button"
                            @click="subTab = 'fasilitas'"
                            :class="subTab === 'fasilitas'
                                ? 'bg-white text-civic-700 shadow-xs dark:bg-slate-800 dark:text-civic-300'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                            class="min-h-10 rounded-lg px-3.5 py-2 text-xs font-bold transition"
                        >
                            Identitas Fasilitas
                        </button>
                        <button
                            type="button"
                            @click="subTab = 'legalitas'"
                            :class="subTab === 'legalitas'
                                ? 'bg-white text-civic-700 shadow-xs dark:bg-slate-800 dark:text-civic-300'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                            class="min-h-10 rounded-lg px-3.5 py-2 text-xs font-bold transition"
                        >
                            Dokumen &amp; Status DLH
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

                <form method="POST" action="{{ route('dashboard.tps3r.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div x-show="subTab === 'fasilitas'" class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="tps3r_nama" class="{{ $labelClass }}">
                                Nama Fasilitas TPS3R <span class="text-rose-500">*</span>
                            </label>
                            <input id="tps3r_nama" name="nama_tps3r" value="{{ old('nama_tps3r', $profile->nama_tps3r) }}" required class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="tps3r_kode" class="{{ $labelClass }}">Kode Registrasi TPS3R</label>
                            <input id="tps3r_kode" name="kode_tps3r" value="{{ old('kode_tps3r', $profile->kode_tps3r) }}" placeholder="Contoh: TPS3R-KTH-01" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="tps3r_kapasitas" class="{{ $labelClass }}">Kapasitas Harian (Kg)</label>
                            <input id="tps3r_kapasitas" type="number" step="0.01" min="1" name="kapasitas_harian_kg" value="{{ old('kapasitas_harian_kg', $profile->kapasitas_harian_kg) }}" placeholder="1000" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="tps3r_kecamatan" class="{{ $labelClass }}">Kecamatan</label>
                            <select id="tps3r_kecamatan" name="kecamatan" class="{{ $inputClass }}">
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach ($districts ?? [] as $district)
                                    <option value="{{ $district }}" @selected(old('kecamatan', $profile->kecamatan) === $district)>{{ $district }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tps3r_kelurahan" class="{{ $labelClass }}">Kelurahan / Desa</label>
                            <input id="tps3r_kelurahan" name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan) }}" placeholder="Nama kelurahan" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="tps3r_lat" class="{{ $labelClass }}">Koordinat Latitude</label>
                            <input id="tps3r_lat" type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $profile->latitude) }}" placeholder="-7.6298" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="tps3r_lng" class="{{ $labelClass }}">Koordinat Longitude</label>
                            <input id="tps3r_lng" type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $profile->longitude) }}" placeholder="111.5239" class="{{ $inputClass }}">
                        </div>

                        <div class="md:col-span-2">
                            <label for="tps3r_alamat" class="{{ $labelClass }}">Alamat Lengkap Fasilitas</label>
                            <textarea id="tps3r_alamat" name="alamat" rows="3" placeholder="Tuliskan alamat lengkap lokasi TPS3R..." class="{{ $inputClass }}">{{ old('alamat', $profile->alamat) }}</textarea>
                        </div>
                    </div>

                    <div x-show="subTab === 'legalitas'" x-cloak class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,1fr)]">
                        <div class="grid content-start gap-5">
                            <div class="rounded-2xl border border-civic-200/80 bg-civic-50/70 p-4 dark:border-civic-900 dark:bg-civic-950/50">
                                <p class="font-display text-sm font-extrabold text-slate-900 dark:text-white">Pengajuan Validasi Legalitas DLH</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                                    Mengisi nomor dokumen atau mengunggah berkas legalitas baru akan otomatis mengirim ulang status verifikasi ke tim Admin DLH Kota Madiun.
                                </p>
                            </div>

                            <div>
                                <label for="tps3r_doc_number" class="{{ $labelClass }}">Nomor Dokumen / NIB / SK Operasional</label>
                                <input id="tps3r_doc_number" name="document_number" value="{{ old('document_number', $verification?->document_number) }}" placeholder="Masukkan nomor NIB atau SK fasilitas" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="tps3r_legal_file" class="{{ $labelClass }}">Unggah Berkas Legalitas (PDF / JPG / PNG, Maks. 5 MB)</label>
                                <div class="mt-1.5 rounded-xl border border-slate-200 bg-slate-50/60 p-3 dark:border-slate-700 dark:bg-slate-950/70">
                                    <input
                                        id="tps3r_legal_file"
                                        type="file"
                                        name="legal_document"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        class="block w-full text-xs text-slate-700 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-civic-100 file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-civic-800 hover:file:bg-civic-200 dark:text-slate-300 dark:file:bg-civic-900 dark:file:text-civic-200"
                                    >
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                                <div>
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Konfigurasi Pembayaran Toko</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelola Midtrans untuk transaksi marketplace TPS3R.</p>
                                </div>
                                <a href="{{ route('dashboard.tps3r.payment-settings') }}" wire:navigate class="civic-button-secondary">
                                    Setup Midtrans
                                </a>
                            </div>
                        </div>

                        <x-civic.entity-verification-panel :verification="$verification" number-label="Nomor Dokumen / NIB" note-label="Catatan Verifier DLH" />
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
                        <a href="{{ route('dashboard.tps3r') }}" wire:navigate class="civic-button-secondary">
                            Kembali ke Dashboard
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
