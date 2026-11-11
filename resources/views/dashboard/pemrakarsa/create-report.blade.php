<x-layouts::app :title="__('Formulir Laporan Lingkungan Semester')">
    <div class="civic-page mx-auto flex w-full max-w-6xl flex-col gap-6 p-2 md:p-4">
        <!-- Header Banner -->
        <div class="civic-hero">
            <div class="civic-hero-grid"></div>
            <div class="flex items-center gap-2 mb-1">
                <span class="civic-kicker">
                    Formulir PPL-F-008
                </span>
                <span class="text-xs text-civic-100">Pelaporan lingkungan semester</span>
            </div>
            <h1 class="civic-display mt-3">
                Susun Laporan Lingkungan
            </h1>
            <p class="mt-2 text-sm text-civic-100">
                <strong class="text-white">{{ $pemrakarsaProfile->nama_instansi ?? auth()->user()->name }}</strong>
                <span class="mx-1.5 text-civic-300">•</span>
                NIB {{ $pemrakarsaProfile->nib ?? '-' }}
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
            <div class="civic-card lg:col-span-2 p-5 sm:p-6">
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

                    <fieldset class="rounded-2xl border border-civic-200 bg-civic-50/70 p-4 dark:border-civic-900 dark:bg-civic-950/20">
                        <legend class="px-2 text-sm font-bold text-civic-900 dark:text-civic-100">Ringkasan pemantauan</legend>
                        <p class="mb-3 text-xs text-zinc-500">Isi nilai utama yang sudah tercantum pada dokumen. Kolom dapat dikosongkan bila tidak relevan.</p>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                <span>Kualitas air / IPAL</span>
                                <input type="text" name="monitoring_data[kualitas_air]" value="{{ old('monitoring_data.kualitas_air') }}" placeholder="Contoh: Memenuhi baku mutu" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-sm font-normal text-zinc-900 focus:border-civic-600 focus:ring-civic-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </label>
                            <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                <span>Emisi / udara</span>
                                <input type="text" name="monitoring_data[emisi_udara]" value="{{ old('monitoring_data.emisi_udara') }}" placeholder="Contoh: Dalam batas izin" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-sm font-normal text-zinc-900 focus:border-civic-600 focus:ring-civic-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </label>
                            <label class="space-y-1 font-semibold text-zinc-700 dark:text-zinc-300">
                                <span>Limbah B3</span>
                                <input type="text" name="monitoring_data[limbah_b3]" value="{{ old('monitoring_data.limbah_b3') }}" placeholder="Contoh: 12,5 kg" class="w-full rounded-lg border border-zinc-300 bg-white p-2.5 text-sm font-normal text-zinc-900 focus:border-civic-600 focus:ring-civic-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </label>
                        </div>
                    </fieldset>

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
                            Berkas Laporan (PDF / DOCX, maks 10MB)
                        </label>
                        <input
                            type="file"
                            name="file_laporan"
                            id="file_laporan"
                            accept=".pdf,.doc,.docx"
                            class="w-full cursor-pointer text-xs text-zinc-500 file:mr-3 file:rounded-xl file:border-0 file:bg-civic-100 file:px-4 file:py-2.5 file:text-xs file:font-semibold file:text-civic-800 hover:file:bg-civic-200 dark:file:bg-civic-900/50 dark:file:text-civic-200"
                        />
                        <p class="mt-1 text-xs text-zinc-500">Berkas boleh ditambahkan nanti saat masih berupa draft, tetapi wajib sebelum dikirim ke DLH.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-4">
                        <button
                            type="submit"
                            name="is_draft"
                            value="1"
                            class="civic-button-secondary"
                        >
                            <flux:icon.bookmark class="size-4" />
                            Simpan Draft
                        </button>
                        <button
                            type="submit"
                            name="status"
                            value="menunggu_verifikasi"
                            class="civic-button-primary"
                        >
                            <flux:icon.paper-airplane class="size-4" />
                            Kirim ke DLH
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
                <div class="civic-card p-6 text-xs text-zinc-600 dark:text-zinc-400">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        <flux:icon.document-text class="size-4" />
                        Dasar Hukum & Ketentuan
                    </h3>
                    <ul class="space-y-2 list-disc list-inside leading-relaxed">
                        <li>Kewajiban pelaporan berkala 6 bulan sekali (per semester) sesuai ketentuan izin lingkungan.</li>
                        <li>Periode Semester 1 dilaporkan paling lambat bulan Juli tahun berjalan.</li>
                        <li>Periode Semester 2 dilaporkan paling lambat bulan Januari tahun berikutnya.</li>
                        <li>Dokumen yang diunggah akan diverifikasi secara resmi oleh tim fungsional DLH Kota Madiun.</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-civic-200 bg-civic-50 p-6 text-xs text-civic-900 dark:border-civic-900/50 dark:bg-civic-950/20 dark:text-civic-200">
                    <h3 class="text-sm font-bold mb-2">Butuh Bantuan?</h3>
                    <p class="leading-relaxed">
                        Jika terdapat kendala pengunggahan atau revisi dokumen, Anda dapat berkonsultasi langsung ke Bidang Tata Lingkungan & Penaatan DLH Kota Madiun.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
