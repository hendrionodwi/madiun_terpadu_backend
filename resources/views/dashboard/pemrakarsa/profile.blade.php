<x-layouts::app :title="__('Profil Perusahaan')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $status = $verification?->status ?? 'draft';
        $statusClass = $status === 'approved' ? 'civic-status-success' : ($status === 'rejected' ? 'civic-status-danger' : 'civic-status-warning');
    @endphp

    <div class="civic-page mx-auto w-full max-w-6xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Pemrakarsa"
            breadcrumbRoute="dashboard.pemrakarsa"
            current="Profil Perusahaan"
            eyebrow="Pemrakarsa Kegiatan"
            title="Profil Perusahaan & Legalitas"
            description="Lengkapi identitas sebelum mengirim laporan lingkungan semester."
            icon="building-office-2"
        >
            <x-slot:heading>Profil Perusahaan & Legalitas</x-slot:heading>
            <x-slot:actions>
                <a href="{{ route('dashboard.pemrakarsa') }}" class="civic-button-secondary">
                    <flux:icon.document-text class="size-4" />
                    Ke laporan semester
                </a>
            </x-slot:actions>
        </x-civic.subpage-header>

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
            <form method="POST" action="{{ route('dashboard.pemrakarsa.profile.update') }}" enctype="multipart/form-data" class="civic-card grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                @csrf
                @method('PATCH')

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 sm:col-span-2">
                    <span>Nama instansi</span>
                    <input name="nama_instansi" value="{{ old('nama_instansi', $profile->nama_instansi) }}" required class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Jenis instansi</span>
                    <input name="jenis_instansi" value="{{ old('jenis_instansi', $profile->jenis_instansi) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>NIB</span>
                    <input name="nib" value="{{ old('nib', $profile->nib) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 sm:col-span-2">
                    <span>Penanggung jawab</span>
                    <input name="penanggung_jawab" value="{{ old('penanggung_jawab', $profile->penanggung_jawab) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 sm:col-span-2">
                    <span>Alamat instansi</span>
                    <textarea name="alamat_instansi" rows="3" class="{{ $fieldClass }}">{{ old('alamat_instansi', $profile->alamat_instansi) }}</textarea>
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Latitude</span>
                    <input type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $profile->latitude) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Longitude</span>
                    <input type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $profile->longitude) }}" class="{{ $fieldClass }}">
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 sm:col-span-2">
                    <span>Dokumen legalitas (PDF/JPG/PNG)</span>
                    <input type="file" name="legal_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-civic-100 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-civic-800 hover:file:bg-civic-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:file:bg-civic-900 dark:file:text-civic-200">
                </label>

                <button type="submit" class="civic-button-primary sm:col-span-2">Simpan profil & ajukan validasi</button>
            </form>

            <aside class="civic-card h-fit p-5 sm:p-6">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Status validasi DLH</p>
                <p class="mt-3 {{ $statusClass }}">{{ strtoupper($status) }}</p>

                <dl class="mt-5 grid gap-4 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">NIB diverifikasi</dt>
                        <dd class="mt-0.5 font-mono text-sm font-bold text-slate-900 dark:text-slate-100">{{ $verification?->document_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Diajukan</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $verification?->submitted_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Catatan DLH</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $verification?->admin_note ?? 'Belum ada catatan.' }}</dd>
                    </div>
                </dl>

                <a href="{{ route('dashboard.pemrakarsa') }}" class="civic-button-secondary mt-6 w-full justify-center">
                    Ke laporan semester
                </a>
            </aside>
        </div>
    </div>
</x-layouts::app>
