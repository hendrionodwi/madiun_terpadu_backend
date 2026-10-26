<x-layouts::app :title="__('Profil TPS3R')">
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 sm:p-6">
        <div>
            <p class="text-sm font-semibold text-teal-700 dark:text-teal-400">Pengelola TPS3R</p>
            <h1 class="text-2xl font-bold text-zinc-950 dark:text-white">Profil Fasilitas & Kapasitas</h1>
            <p class="mt-1 text-sm text-zinc-500">Data ini dipakai pada penerimaan setoran, rekap volume, dan validasi DLH.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300"><ul class="list-disc space-y-1 ps-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
            <form method="POST" action="{{ route('dashboard.tps3r.profile.update') }}" enctype="multipart/form-data" class="grid gap-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2">
                @csrf
                @method('PATCH')
                <label class="grid gap-2 text-sm font-medium sm:col-span-2">Nama fasilitas<input name="nama_tps3r" value="{{ old('nama_tps3r', $profile->nama_tps3r) }}" required class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <label class="grid gap-2 text-sm font-medium">Kode TPS3R<input name="kode_tps3r" value="{{ old('kode_tps3r', $profile->kode_tps3r) }}" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <label class="grid gap-2 text-sm font-medium">Kapasitas harian (kg)<input type="number" step="0.01" min="1" name="kapasitas_harian_kg" value="{{ old('kapasitas_harian_kg', $profile->kapasitas_harian_kg) }}" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <label class="grid gap-2 text-sm font-medium sm:col-span-2">Alamat<textarea name="alamat" rows="3" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">{{ old('alamat', $profile->alamat) }}</textarea></label>
                <label class="grid gap-2 text-sm font-medium">Kelurahan<input name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan) }}" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <label class="grid gap-2 text-sm font-medium">Kecamatan<select name="kecamatan" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"><option value="">Pilih kecamatan</option>@foreach ($districts ?? [] as $district)<option value="{{ $district }}" @selected(old('kecamatan', $profile->kecamatan) === $district)>{{ $district }}</option>@endforeach</select></label>
                <label class="grid gap-2 text-sm font-medium">Latitude<input type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $profile->latitude) }}" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <label class="grid gap-2 text-sm font-medium">Longitude<input type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $profile->longitude) }}" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <div class="border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:col-span-2"><h2 class="font-bold">Dokumen legalitas</h2><p class="text-xs text-zinc-500">Mengunggah dokumen akan mengirim ulang profil untuk validasi DLH.</p></div>
                <label class="grid gap-2 text-sm font-medium">Nomor dokumen/NIB<input name="document_number" value="{{ old('document_number', $verification?->document_number) }}" class="rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></label>
                <label class="grid gap-2 text-sm font-medium">Berkas PDF/JPG/PNG<input type="file" name="legal_document" accept=".pdf,.jpg,.jpeg,.png" class="rounded-xl border border-zinc-300 p-2 text-xs dark:border-zinc-700"></label>
                <button class="rounded-xl bg-teal-700 px-5 py-3 text-sm font-bold text-white hover:bg-teal-800 sm:col-span-2">Simpan profil & ajukan validasi</button>
            </form>

            <aside class="h-fit rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Status validasi DLH</p>
                @php($status = $verification?->status ?? 'draft')
                <p class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ strtoupper($status) }}</p>
                <dl class="mt-5 grid gap-4 text-sm"><div><dt class="text-zinc-500">Nomor dokumen</dt><dd class="font-semibold">{{ $verification?->document_number ?? '-' }}</dd></div><div><dt class="text-zinc-500">Diajukan</dt><dd class="font-semibold">{{ $verification?->submitted_at?->format('d M Y H:i') ?? '-' }}</dd></div><div><dt class="text-zinc-500">Catatan admin</dt><dd class="font-semibold">{{ $verification?->admin_note ?? 'Belum ada catatan.' }}</dd></div></dl>
            </aside>
        </div>
    </div>
</x-layouts::app>
