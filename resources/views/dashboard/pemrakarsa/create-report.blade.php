<x-layouts::app :title="__('Formulir Laporan Lingkungan Semester')">
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-2 md:p-4">
        <!-- Header Banner -->
        <div class="rounded-2xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-6 text-white shadow-lg">
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center rounded-full bg-indigo-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-200 border border-indigo-500/30">
                    Formulir PPL-F-008
                </span>
                <span class="text-xs text-indigo-200">Kewajiban Pelaporan Dokumen Lingkungan Semester</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Unggah Laporan Pelaksanaan Dokumen Lingkungan
            </h1>
            <p class="text-sm text-indigo-100/90 mt-1">
                Instansi: <strong class="text-white">{{ $pemrakarsaProfile->nama_instansi ?? auth()->user()->name }}</strong> • NIB: <strong class="text-white">{{ $pemrakarsaProfile->nib ?? '-' }}</strong>
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                <p class="font-bold mb-1">Terdapat kesalahan pada formulir pelaporan:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Area (2 Kolom) -->
            <div class="lg:col-span-2 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 shadow-sm">
                <form method="POST" action="{{ route('dashboard.pemrakarsa.store-laporan') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label for="judul_laporan" class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Judul Laporan Pelaksanaan <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="judul_laporan"
                            id="judul_laporan"
                            value="{{ old('judul_laporan') }}"
                            placeholder="Contoh: Laporan Pemantauan Lingkungan Semester I 2026 PT Madiun Maju"
                            required
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2.5 text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="periode_semester" class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Periode Semester <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="periode_semester"
                                id="periode_semester"
                                required
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2.5 text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 text-sm"
                            >
                                <option value="Semester 1 (Jan - Jun)" {{ old('periode_semester') == 'Semester 1 (Jan - Jun)' ? 'selected' : '' }}>Semester 1 (Jan - Jun)</option>
                                <option value="Semester 2 (Jul - Des)" {{ old('periode_semester') == 'Semester 2 (Jul - Des)' ? 'selected' : '' }}>Semester 2 (Jul - Des)</option>
                            </select>
                        </div>

                        <div>
                            <label for="tahun" class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Tahun Pelaporan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="tahun"
                                id="tahun"
                                min="2020"
                                max="2035"
                                value="{{ old('tahun', date('Y')) }}"
                                required
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2.5 text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 text-sm"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="jenis_dokumen" class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Jenis Dokumen Lingkungan <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="jenis_dokumen"
                                id="jenis_dokumen"
                                required
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2.5 text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 text-sm"
                            >
                                <option value="SPPL" {{ old('jenis_dokumen') == 'SPPL' ? 'selected' : '' }}>SPPL (Surat Pernyataan Kesanggupan Pengelolaan)</option>
                                <option value="UKL-UPL" {{ old('jenis_dokumen') == 'UKL-UPL' ? 'selected' : '' }}>UKL-UPL (Upaya Pengelolaan & Pemantauan)</option>
                                <option value="AMDAL" {{ old('jenis_dokumen') == 'AMDAL' ? 'selected' : '' }}>AMDAL (Analisis Mengenai Dampak Lingkungan)</option>
                            </select>
                        </div>

                        <div>
                            <label for="timbulan_sampah_kg" class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Rata-rata Timbulan Sampah (Kg/Bulan)
                            </label>
                            <input
                                type="number"
                                step="0.1"
                                name="timbulan_sampah_kg"
                                id="timbulan_sampah_kg"
                                value="{{ old('timbulan_sampah_kg') }}"
                                placeholder="Contoh: 1500.5"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2.5 text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="metode_pengelolaan" class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Ringkasan Metode & Upaya Pengelolaan Lingkungan
                        </label>
                        <textarea
                            name="metode_pengelolaan"
                            id="metode_pengelolaan"
                            rows="4"
                            placeholder="Jelaskan sarana pemilahan sampah, kerjasama dengan TPS3R/pihak ketiga berizin, pengelolaan limbah B3, serta pemantauan baku mutu..."
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2.5 text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 text-sm"
                        >{{ old('metode_pengelolaan') }}</textarea>
                    </div>

                    <div>
                        <label for="file_laporan" class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Unggah Berkas Laporan (PDF / DOCX, maks 10MB) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="file"
                            name="file_laporan"
                            id="file_laporan"
                            accept=".pdf,.doc,.docx"
                            required
                            class="w-full text-xs text-zinc-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/50 dark:file:text-indigo-300 cursor-pointer"
                        />
                        <p class="text-[11px] text-zinc-500 mt-1">Format disarankan: Dokumen PDF yang memuat tanda tangan basah/elektronik penanggung jawab.</p>
                    </div>

                    <div class="pt-4 flex items-center gap-3">
                        <button
                            type="submit"
                            class="rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-3 px-6 shadow transition flex items-center justify-center gap-2 cursor-pointer text-sm"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            Kirim Dokumen Laporan ke DLH
                        </button>
                        <a
                            href="{{ route('dashboard.pemrakarsa.reports') }}"
                            class="rounded-xl border border-zinc-300 dark:border-zinc-700 px-5 py-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50"
                        >
                            Batal
                        </a>
                    </div>
                </form>
            </div>

            <!-- Petunjuk Teknis & Regulasi (1 Kolom) -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 shadow-sm text-xs text-zinc-600 dark:text-zinc-400">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3 flex items-center gap-2">
                        <span>📜</span> Dasar Hukum & Ketentuan
                    </h3>
                    <ul class="space-y-2 list-disc list-inside leading-relaxed">
                        <li>Kewajiban pelaporan berkala 6 bulan sekali (per semester) sesuai ketentuan izin lingkungan.</li>
                        <li>Periode Semester 1 dilaporkan paling lambat bulan Juli tahun berjalan.</li>
                        <li>Periode Semester 2 dilaporkan paling lambat bulan Januari tahun berikutnya.</li>
                        <li>Dokumen yang diunggah akan diverifikasi secara resmi oleh tim fungsional DLH Kota Madiun.</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/50 dark:bg-indigo-950/20 p-6 text-xs text-indigo-900 dark:text-indigo-200">
                    <h3 class="text-sm font-bold mb-2">Butuh Bantuan?</h3>
                    <p class="leading-relaxed">
                        Jika terdapat kendala pengunggahan atau revisi dokumen, Anda dapat berkonsultasi langsung ke Bidang Tata Lingkungan & Penaatan DLH Kota Madiun.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
