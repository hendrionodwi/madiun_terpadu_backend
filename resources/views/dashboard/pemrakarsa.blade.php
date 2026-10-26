<x-layouts::app :title="__('Portal Pelaporan Lingkungan Pemrakarsa Kegiatan')">
    <div class="civic-page">
        <x-civic.page-hero
            eyebrow="Portal Pelaporan Lingkungan"
            :title="$pemrakarsaProfile->nama_instansi ?? auth()->user()->name"
            description="Sampaikan laporan pelaksanaan dokumen lingkungan dan pantau proses verifikasi DLH Kota Madiun secara transparan."
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.pemrakarsa.reports.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-civic-900 shadow-sm hover:bg-civic-50">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5h10.5A2.25 2.25 0 0 0 19.5 17.25v-10.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                    Unggah laporan
                </a>
                <a href="{{ route('dashboard.pemrakarsa.profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur-sm hover:bg-white/20">
                    Profil & legalitas
                </a>
            </x-slot:actions>

            <x-slot:meta>
                <div class="grid gap-3 text-xs text-civic-100/90 sm:grid-cols-3">
                    <div><span class="block text-[0.6rem] font-bold uppercase tracking-wider text-civic-300">Jenis instansi</span><strong class="mt-0.5 block text-white">{{ $pemrakarsaProfile->jenis_instansi ?? 'Komersial / Usaha' }}</strong></div>
                    <div><span class="block text-[0.6rem] font-bold uppercase tracking-wider text-civic-300">Nomor NIB</span><strong class="mt-0.5 block font-mono text-white">{{ $pemrakarsaProfile->nib ?? '-' }}</strong></div>
                    <div><span class="block text-[0.6rem] font-bold uppercase tracking-wider text-civic-300">Status entitas</span><strong class="mt-0.5 inline-flex items-center gap-2 text-white"><span class="size-2 rounded-full bg-emerald-300"></span>Terdaftar aktif</strong></div>
                </div>
            </x-slot:meta>
        </x-civic.page-hero>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <p class="font-display font-extrabold">Periksa kembali formulir laporan:</p>
                <ul class="mt-2 list-disc space-y-1 ps-5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section aria-labelledby="ringkasan-laporan-title">
            <div class="mb-3">
                <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.14em] text-civic-700 dark:text-civic-300">Ringkasan kepatuhan</p>
                <h2 id="ringkasan-laporan-title" class="mt-1 font-display text-lg font-extrabold text-slate-950 dark:text-white">Status pelaporan perusahaan</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-civic.stat-card label="Total laporan" :value="$stats['total']" suffix="dokumen" description="Seluruh laporan semester yang dikirim" :href="route('dashboard.pemrakarsa.reports', ['status' => 'all'])">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.63a3.38 3.38 0 0 0-3.38-3.37h-1.5A1.13 1.13 0 0 1 13.5 7.12v-1.5a3.38 3.38 0 0 0-3.38-3.37H8.25m.75 12h6m-6 3h3m-1.5-15H5.62c-.62 0-1.12.5-1.12 1.13v17.25c0 .62.5 1.12 1.12 1.12h12.76c.62 0 1.12-.5 1.12-1.12V11.25a9 9 0 0 0-9-9Z" /></svg></x-slot:icon>
                </x-civic.stat-card>
                <x-civic.stat-card label="Disetujui DLH" :value="$stats['disetujui']" suffix="dokumen" description="Laporan yang telah dinyatakan sesuai" :href="route('dashboard.pemrakarsa.reports', ['status' => 'disetujui'])" tone="sky">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12.75 2.25 2.25L15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
                </x-civic.stat-card>
                <x-civic.stat-card label="Menunggu verifikasi" :value="$stats['menunggu']" suffix="dokumen" description="Sedang berada dalam antrean pemeriksaan" :href="route('dashboard.pemrakarsa.reports', ['status' => 'menunggu_verifikasi'])" tone="amber">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
                </x-civic.stat-card>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            <x-civic.section-card
                class="xl:col-span-2"
                title="Unggah Laporan Semester Baru"
                description="Kirim dokumen pelaksanaan SPPL, UKL-UPL, atau AMDAL untuk diverifikasi DLH."
            >
                <x-slot:action><a href="{{ route('dashboard.pemrakarsa.reports.create') }}" class="civic-link">Form penuh <span aria-hidden="true">&rarr;</span></a></x-slot:action>

                <form method="POST" action="{{ route('dashboard.pemrakarsa.store-laporan') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="judul_laporan" class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Judul Laporan <span class="text-red-600">*</span></label>
                        <input type="text" name="judul_laporan" id="judul_laporan" value="{{ old('judul_laporan') }}" placeholder="Laporan Pemantauan Lingkungan Semester I 2026" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 outline-none placeholder:text-slate-400 focus:border-civic-600 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-civic-900" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="periode_semester" class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Periode Semester <span class="text-red-600">*</span></label>
                            <select name="periode_semester" id="periode_semester" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 outline-none focus:border-civic-600 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-civic-900">
                                <option value="Semester 1 (Jan - Jun)" @selected(old('periode_semester') === 'Semester 1 (Jan - Jun)')>Semester 1 (Jan - Jun)</option>
                                <option value="Semester 2 (Jul - Des)" @selected(old('periode_semester') === 'Semester 2 (Jul - Des)')>Semester 2 (Jul - Des)</option>
                            </select>
                        </div>
                        <div>
                            <label for="tahun" class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Tahun <span class="text-red-600">*</span></label>
                            <input type="number" name="tahun" id="tahun" value="{{ old('tahun', date('Y')) }}" min="2020" max="2035" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 outline-none focus:border-civic-600 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-civic-900" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jenis_dokumen" class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Jenis Dokumen Lingkungan <span class="text-red-600">*</span></label>
                            <select name="jenis_dokumen" id="jenis_dokumen" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 outline-none focus:border-civic-600 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-civic-900">
                                <option value="SPPL" @selected(old('jenis_dokumen') === 'SPPL')>SPPL</option>
                                <option value="UKL-UPL" @selected(old('jenis_dokumen') === 'UKL-UPL')>UKL-UPL</option>
                                <option value="AMDAL" @selected(old('jenis_dokumen') === 'AMDAL')>AMDAL</option>
                            </select>
                        </div>
                        <div>
                            <label for="timbulan_sampah_kg" class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Timbulan (kg/bulan)</label>
                            <input type="number" step="0.1" name="timbulan_sampah_kg" id="timbulan_sampah_kg" value="{{ old('timbulan_sampah_kg') }}" placeholder="Contoh: 1250.5" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 outline-none placeholder:text-slate-400 focus:border-civic-600 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-civic-900" />
                        </div>
                    </div>

                    <div>
                        <label for="metode_pengelolaan" class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Metode & Upaya Pengelolaan Sampah</label>
                        <textarea name="metode_pengelolaan" id="metode_pengelolaan" rows="3" placeholder="Jelaskan sarana pemilahan, kerja sama TPS3R, dan pengelolaan limbah..." class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-950 outline-none placeholder:text-slate-400 focus:border-civic-600 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-civic-900">{{ old('metode_pengelolaan') }}</textarea>
                    </div>

                    <div>
                        <label for="file_laporan" class="mb-1.5 block text-xs font-bold text-slate-700 dark:text-slate-300">Berkas Laporan <span class="text-red-600">*</span></label>
                        <label for="file_laporan" class="flex cursor-pointer items-center gap-3 rounded-2xl border border-dashed border-civic-300 bg-civic-50 px-4 py-4 hover:border-civic-500 hover:bg-civic-100/70 dark:border-civic-800 dark:bg-civic-950/35">
                            <span class="civic-icon-tile"><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5h10.5A2.25 2.25 0 0 0 19.5 17.25v-10.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></span>
                            <span>
                                <span class="block text-xs font-extrabold text-civic-900 dark:text-civic-100">Pilih PDF, DOC, atau DOCX</span>
                                <span class="mt-1 block text-[0.68rem] text-civic-700 dark:text-civic-300">Ukuran maksimal 10 MB</span>
                            </span>
                        </label>
                        <input type="file" name="file_laporan" id="file_laporan" accept=".pdf,.doc,.docx" required class="sr-only" />
                    </div>

                    <button type="submit" class="civic-button-primary w-full">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5h10.5A2.25 2.25 0 0 0 19.5 17.25v-10.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        Kirim laporan ke DLH
                    </button>
                </form>
            </x-civic.section-card>

            <x-civic.section-card
                class="xl:col-span-3"
                title="Riwayat pengajuan laporan"
                description="Pantau status dan catatan verifikasi setiap dokumen yang telah dikirim."
                :padded="false"
            >
                <x-slot:action><a href="{{ route('dashboard.pemrakarsa.reports') }}" class="civic-link">Lihat semua <span aria-hidden="true">&rarr;</span></a></x-slot:action>

                <div class="overflow-x-auto">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>Dokumen</th>
                                <th>Periode</th>
                                <th>Timbulan</th>
                                <th>Status DLH</th>
                                <th>Berkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayatLaporan as $lap)
                                <tr>
                                    <td>
                                        <p class="font-display text-sm font-bold text-slate-950 dark:text-white">{{ $lap->judul_laporan }}</p>
                                        <span class="mt-1.5 inline-flex rounded-md bg-slate-100 px-2 py-0.5 font-mono text-[0.65rem] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $lap->jenis_dokumen }}</span>
                                    </td>
                                    <td class="text-xs text-slate-600 dark:text-slate-400">{{ $lap->periode_semester }}<br><span class="text-slate-400">Tahun {{ $lap->tahun }}</span></td>
                                    <td class="font-display text-xs font-bold text-slate-800 dark:text-slate-200">{{ $lap->timbulan_sampah_kg ? number_format($lap->timbulan_sampah_kg, 1, ',', '.').' kg' : '-' }}</td>
                                    <td>
                                        <span @class([
                                            'civic-status-warning' => $lap->status === 'menunggu_verifikasi',
                                            'civic-status-success' => $lap->status === 'disetujui',
                                            'civic-status-danger' => ! in_array($lap->status, ['menunggu_verifikasi', 'disetujui'], true),
                                        ])>{{ ucfirst(str_replace('_', ' ', $lap->status)) }}</span>
                                        @if ($lap->catatan_verifikasi)
                                            <p class="mt-2 max-w-xs text-[0.68rem] italic leading-5 text-slate-500">{{ $lap->catatan_verifikasi }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ asset('storage/'.$lap->file_path) }}" target="_blank" class="civic-link">Unduh <span aria-hidden="true">&darr;</span></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="civic-empty m-4">Belum ada laporan semester. Gunakan formulir untuk mengirim dokumen pertama.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-civic.section-card>
        </div>
    </div>
</x-layouts::app>
