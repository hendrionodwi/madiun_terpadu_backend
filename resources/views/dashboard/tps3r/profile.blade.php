<x-layouts::app :title="__('Profil TPS3R')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $status = $verification?->status ?? 'draft';
        $statusClass = $status === 'approved' ? 'civic-status-success' : ($status === 'rejected' ? 'civic-status-danger' : 'civic-status-warning');
    @endphp

    <div class="civic-page mx-auto w-full max-w-6xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Profil Fasilitas"
            eyebrow="Pengelola TPS3R"
            title="Profil Fasilitas & Kapasitas"
            description="Data ini dipakai pada penerimaan setoran, rekap volume, dan validasi DLH."
            icon="building-storefront"
        />

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
            <form method="POST" action="{{ route('dashboard.tps3r.profile.update') }}" enctype="multipart/form-data" class="civic-card grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                @csrf
                @method('PATCH')

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 sm:col-span-2">
                    <span>Nama fasilitas</span>
                    <input name="nama_tps3r" value="{{ old('nama_tps3r', $profile->nama_tps3r) }}" required class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Kode TPS3R</span>
                    <input name="kode_tps3r" value="{{ old('kode_tps3r', $profile->kode_tps3r) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Kapasitas harian (kg)</span>
                    <input type="number" step="0.01" min="1" name="kapasitas_harian_kg" value="{{ old('kapasitas_harian_kg', $profile->kapasitas_harian_kg) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 sm:col-span-2">
                    <span>Alamat</span>
                    <textarea name="alamat" rows="3" class="{{ $fieldClass }}">{{ old('alamat', $profile->alamat) }}</textarea>
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Kelurahan</span>
                    <input name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Kecamatan</span>
                    <select name="kecamatan" class="{{ $fieldClass }}">
                        <option value="">Pilih kecamatan</option>
                        @foreach ($districts ?? [] as $district)
                            <option value="{{ $district }}" @selected(old('kecamatan', $profile->kecamatan) === $district)>{{ $district }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Latitude</span>
                    <input type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $profile->latitude) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Longitude</span>
                    <input type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $profile->longitude) }}" class="{{ $fieldClass }}">
                </label>

                <div class="border-t border-slate-100 pt-5 dark:border-slate-800 sm:col-span-2">
                    <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Dokumen legalitas</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Mengunggah dokumen akan mengirim ulang profil untuk validasi DLH.</p>
                </div>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Nomor dokumen/NIB</span>
                    <input name="document_number" value="{{ old('document_number', $verification?->document_number) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Berkas PDF/JPG/PNG</span>
                    <input type="file" name="legal_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-civic-100 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-civic-800 hover:file:bg-civic-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:file:bg-civic-900 dark:file:text-civic-200">
                </label>

                <button type="submit" class="civic-button-primary sm:col-span-2">Simpan profil & ajukan validasi</button>
            </form>

            <aside class="civic-card h-fit p-5 sm:p-6">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Status validasi DLH</p>
                <p class="mt-3 {{ $statusClass }}">{{ strtoupper($status) }}</p>

                <dl class="mt-5 grid gap-4 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Nomor dokumen</dt>
                        <dd class="mt-0.5 font-mono text-sm font-bold text-slate-900 dark:text-slate-100">{{ $verification?->document_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Diajukan</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $verification?->submitted_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Catatan admin</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $verification?->admin_note ?? 'Belum ada catatan.' }}</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </div>
</x-layouts::app>
