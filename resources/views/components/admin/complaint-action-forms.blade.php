@props([
    'aduan',
    'petugasList',
    'activeRoutes' => collect(),
])

<div class="grid grid-cols-1 gap-3 2xl:grid-cols-2">
    <!-- Form Disposisi Petugas (SRS Butir 2) -->
    <form method="POST" action="{{ route('dashboard.admin.complaints.assign', $aduan) }}" class="min-w-0 rounded-2xl border border-sky-200 bg-sky-50/70 p-4 dark:border-sky-900/60 dark:bg-sky-950/20">
        @csrf
        @method('PATCH')

        <div class="flex items-start gap-3">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/60 dark:text-sky-200" aria-hidden="true">
                <flux:icon.user class="size-5" />
            </span>
            <div class="min-w-0">
                <h4 class="font-display font-bold text-slate-950 dark:text-white">Penugasan Lapangan</h4>
                <p class="mt-0.5 text-xs leading-4 text-sky-800/80 dark:text-sky-300/80">Pilih petugas dan berikan instruksi kerja yang jelas.</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label class="min-w-0 space-y-1.5">
                <span class="block font-semibold text-slate-700 dark:text-slate-300">Petugas lapangan</span>
                <select name="petugas_id" required class="min-h-10 w-full min-w-0 rounded-xl border border-sky-200 bg-white px-3 py-2 font-semibold text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-sky-900 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Pilih petugas</option>
                    @foreach ($petugasList as $p)
                        <option value="{{ $p->id }}" @selected($aduan->assigned_petugas_id === $p->id)>
                            {{ $p->name }} ({{ $p->petugasLapanganProfile->nama_wilayah_tugas ?? $p->petugasLapanganProfile->id_wilayah_rute ?? 'Armada Kota' }})
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="min-w-0 space-y-1.5">
                <span class="block font-semibold text-slate-700 dark:text-slate-300">Instruksi DLH</span>
                <input type="text" name="instruksi_admin" placeholder="Contoh: verifikasi dan bersihkan lokasi" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-sky-200 bg-white px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-sky-900 dark:bg-slate-900 dark:text-slate-100">
            </label>
        </div>

        <x-admin.complaint-assignment-fields :complaint="$aduan" :active-routes="$activeRoutes" />

        <div class="mt-3 flex justify-end">
            <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-sky-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-slate-900">
                <flux:icon.arrow-right class="size-3.5" />
                {{ $aduan->assigned_petugas_id ? 'Perbarui Penugasan' : 'Tugaskan Petugas' }}
            </button>
        </div>
    </form>

    <!-- Form Finalisasi Status Akhir (SRS Butir 3) -->
    <form method="POST" action="{{ route('dashboard.admin.complaints.finalize', $aduan) }}" class="min-w-0 rounded-2xl border border-civic-200 bg-civic-50/70 p-4 dark:border-civic-900/60 dark:bg-civic-950/20">
        @csrf
        @method('PATCH')

        <div class="flex items-start gap-3">
            <span class="civic-icon-tile !size-9" aria-hidden="true">✓</span>
            <div class="min-w-0">
                <h4 class="font-display font-bold text-slate-950 dark:text-white">Finalisasi Aduan</h4>
                <p class="mt-0.5 text-xs leading-4 text-civic-700/90 dark:text-civic-300/80">Perbarui hasil penanganan dan catatan akhir DLH.</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label class="min-w-0 space-y-1.5">
                <span class="block font-semibold text-slate-700 dark:text-slate-300">Status penanganan</span>
                <select name="status" class="min-h-10 w-full min-w-0 rounded-xl border border-civic-200 bg-white px-3 py-2 font-semibold text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-500/20 dark:border-civic-900 dark:bg-slate-900 dark:text-slate-100">
                    <option value="ditindaklanjuti" {{ in_array($aduan->status, ['ditindaklanjuti', 'dalam_proses']) ? 'selected' : '' }}>Ditindaklanjuti (Proses Penanganan / Armada)</option>
                    <option value="selesai" {{ $aduan->status === 'selesai' ? 'selected' : '' }}>Selesai (Sudah Bersih & Tertangani)</option>
                    <option value="ditolak" {{ $aduan->status === 'ditolak' ? 'selected' : '' }}>Tolak Aduan (Tidak Valid / Hoax)</option>
                </select>
            </label>

            <label class="min-w-0 space-y-1.5">
                <span class="block font-semibold text-slate-700 dark:text-slate-300">Catatan tindak lanjut</span>
                <input type="text" name="admin_note" placeholder="Tambahkan catatan akhir" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-civic-200 bg-white px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-civic-500 focus:ring-2 focus:ring-civic-500/20 dark:border-civic-900 dark:bg-slate-900 dark:text-slate-100">
            </label>
        </div>

        <div class="mt-3 flex justify-end">
            <button type="submit" class="civic-button-primary !min-h-10 w-full !text-xs sm:w-auto">
                <span aria-hidden="true">✓</span>
                Simpan Status Akhir
            </button>
        </div>
    </form>
</div>
