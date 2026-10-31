<x-layouts::app :title="__('Kelola Pengaduan Masyarakat - Admin DLH')">
    <div class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Pengaduan Masyarakat</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Kelola Pengaduan Masyarakat (PPL-F-001)</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Pantau laporan tumpukan sampah liar, tinjau hasil verifikasi petugas lapangan, dan finalisasi tindak lanjut.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 text-xs font-bold">
                    {{ $statusCounts['menunggu_verifikasi'] + $statusCounts['dalam_proses'] }} Menunggu / Diproses
                </span>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <a href="{{ route('dashboard.admin.complaints', ['kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ ! $currentStatus ? 'bg-emerald-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Semua Status ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('dashboard.admin.complaints', ['status' => 'menunggu_verifikasi', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'menunggu_verifikasi' ? 'bg-amber-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Menunggu ({{ $statusCounts['menunggu_verifikasi'] }})
                </a>
                <a href="{{ route('dashboard.admin.complaints', ['status' => 'dalam_proses', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'dalam_proses' ? 'bg-blue-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Dalam Proses ({{ $statusCounts['dalam_proses'] }})
                </a>
                <a href="{{ route('dashboard.admin.complaints', ['status' => 'selesai', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'selesai' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Selesai ({{ $statusCounts['selesai'] }})
                </a>
                <a href="{{ route('dashboard.admin.complaints', ['status' => 'ditolak', 'kecamatan' => $currentDistrict, 'kategori' => $currentCategory]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentStatus === 'ditolak' ? 'bg-red-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Ditolak ({{ $statusCounts['ditolak'] }})
                </a>
            </div>

            <!-- Filter Kecamatan & Kategori -->
            <form method="GET" action="{{ route('dashboard.admin.complaints') }}" class="flex items-center flex-wrap gap-2">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <select name="kategori" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-medium text-xs">
                    <option value="">Semua Kategori Aduan</option>
                    @if (isset($complaintCategories) && $complaintCategories->isNotEmpty())
                        @foreach ($complaintCategories as $cat)
                            <option value="{{ $cat->code }}" {{ $currentCategory === $cat->code ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    @else
                        <option value="Sampah_Liar" {{ $currentCategory === 'Sampah_Liar' ? 'selected' : '' }}>Tumpukan Sampah Liar</option>
                        <option value="sampah_liar" {{ $currentCategory === 'sampah_liar' ? 'selected' : '' }}>Sampah Liar</option>
                        <option value="tps_meluap" {{ $currentCategory === 'tps_meluap' ? 'selected' : '' }}>TPS / Kontainer Meluap</option>
                        <option value="saluran_tersumbat" {{ $currentCategory === 'saluran_tersumbat' ? 'selected' : '' }}>Saluran Tersumbat</option>
                        <option value="pembakaran_sampah" {{ $currentCategory === 'pembakaran_sampah' ? 'selected' : '' }}>Pembakaran Sampah</option>
                        <option value="pencemaran_lingkungan" {{ $currentCategory === 'pencemaran_lingkungan' ? 'selected' : '' }}>Pencemaran Lingkungan</option>
                    @endif
                </select>
                <select name="kecamatan" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-medium text-xs">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($districts ?? ['Kartoharjo', 'Manguharjo', 'Taman'] as $d)
                        <option value="{{ $d }}" {{ $currentDistrict === $d ? 'selected' : '' }}>Kec. {{ $d }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Daftar Kartu Tiket Pengaduan -->
        <div class="space-y-4">
            @forelse ($complaints as $aduan)
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <!-- Header Aduan -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-xs font-black text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-md">
                                #{{ $aduan->nomor_tiket }}
                            </span>
                            @if ($aduan->is_anonim)
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-zinc-800 text-zinc-200 dark:bg-zinc-700 flex items-center gap-1" title="Pelapor memilih merahasiakan identitas dari publik dan petugas">
                                    <flux:icon.lock-closed class="size-3" />
                                    Anonim
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                <span class="inline-flex items-center gap-1.5">
                                    <flux:icon.folder-open class="size-3.5" />
                                    {{ (isset($complaintCategories) ? $complaintCategories->firstWhere('code', $aduan->kategori_masalah)?->name : null) ?? ucwords(str_replace('_', ' ', $aduan->kategori_masalah ?? 'sampah_liar')) }}
                                </span>
                            </span>
                            <span class="text-xs text-zinc-400">•</span>
                            <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                                Pelapor: {{ $aduan->pelapor->name ?? 'Warga' }}
                                @if (!$aduan->is_anonim && $aduan->pelapor?->phone_number)
                                    ({{ $aduan->pelapor->phone_number }})
                                @endif
                            </span>
                            <span class="text-xs text-zinc-400">•</span>
                            <span class="text-xs text-zinc-500">{{ $aduan->created_at->translatedFormat('d M Y, H:i') }} ({{ $aduan->created_at->diffForHumans() }})</span>
                        </div>
                        <span class="self-start sm:self-auto px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $aduan->status === 'selesai' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' :
                               ($aduan->status === 'ditindaklanjuti' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' :
                               ($aduan->status === 'diverifikasi_lapangan' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' :
                               ($aduan->status === 'ditugaskan' || $aduan->status === 'dalam_proses' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' :
                               ($aduan->status === 'ditolak' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300')))) }}">
                            {{ $aduan->status_label ?? ucfirst(str_replace('_', ' ', $aduan->status)) }}
                        </span>
                    </div>

                    <!-- Detail Isi Aduan -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <div class="md:col-span-2 space-y-2">
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $aduan->judul }}</h3>
                            <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">{{ $aduan->deskripsi }}</p>
                            <div class="flex items-center gap-3 text-zinc-500 mt-2">
                                <span class="inline-flex items-center gap-1.5"><flux:icon.map-pin class="size-3.5" /> {{ $aduan->lokasi }}</span>
                                @if ($aduan->kecamatan)
                                    <span>• Kec. {{ $aduan->kecamatan }}</span>
                                @endif
                                @if ($aduan->latitude && $aduan->longitude)
                                    <span>• GPS: {{ $aduan->latitude }}, {{ $aduan->longitude }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Foto Bukti Awal -->
                        <div class="md:col-span-1">
                            @if ($aduan->foto)
                                <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 max-h-36">
                                    <img src="{{ $aduan->foto_url ?? asset('storage/' . $aduan->foto) }}" alt="Foto Aduan" class="w-full h-36 object-cover hover:scale-105 transition">
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1.5 rounded-xl border border-dashed border-zinc-300 p-4 text-center text-zinc-400 dark:border-zinc-700">
                                    <flux:icon.photo class="size-5" />
                                    <span>Tidak ada lampiran foto</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Status Penugasan Petugas Lapangan (SRS Butir 2) -->
                    @if ($aduan->assignedPetugas)
                        <div class="flex items-center gap-2.5 p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/50 text-xs text-blue-900 dark:text-blue-200">
                            <flux:icon.user class="size-5 shrink-0" />
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold">Petugas Ditugaskan ke Lokasi:</span>
                                    <span class="bg-blue-200 dark:bg-blue-900 text-blue-900 dark:text-blue-100 font-extrabold px-2 py-0.5 rounded-md">{{ $aduan->assignedPetugas->name }}</span>
                                    @if ($aduan->assigned_at)
                                        <span class="text-zinc-500 dark:text-zinc-400">• Disposisi: {{ $aduan->assigned_at->translatedFormat('d M Y, H:i') }}</span>
                                    @endif
                                </div>
                                @if ($aduan->instruksi_admin)
                                    <p class="mt-1 text-zinc-700 dark:text-zinc-300 italic bg-white/60 dark:bg-zinc-900/60 p-2 rounded-lg border border-blue-100 dark:border-blue-950">
                                        <strong>Instruksi DLH:</strong> "{{ $aduan->instruksi_admin }}"
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Hasil Investigasi Petugas Lapangan (jika sudah diverifikasi) -->
                    @if ($aduan->verifications->isNotEmpty())
                        <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/60 dark:bg-emerald-950/20 dark:border-emerald-900/40 text-xs">
                            <h4 class="font-bold text-emerald-900 dark:text-emerald-300 mb-2 flex items-center gap-1.5">
                                <flux:icon.magnifying-glass class="size-4" />
                                Hasil Verifikasi & Tindak Lanjut Lapangan (PPL-F-004):
                            </h4>
                            @foreach ($aduan->verifications as $verif)
                                <div class="space-y-1.5 text-zinc-700 dark:text-zinc-300">
                                    <p class="font-semibold">Petugas: {{ $verif->petugas->name ?? 'Petugas DLH' }} • {{ \Carbon\Carbon::parse($verif->tanggal_verifikasi)->translatedFormat('d M Y') }}</p>
                                    <p class="italic text-zinc-600 dark:text-zinc-400">Catatan Temuan: "{{ $verif->catatan_petugas ?? 'Sampah telah ditinjau.' }}"</p>
                                    @if ($verif->rekomendasi_tindak_lanjut)
                                        <div class="p-2 rounded-lg bg-emerald-100/70 dark:bg-emerald-900/40 border border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 font-medium">
                                            <strong>Rekomendasi Tindak Lanjut:</strong> {{ $verif->rekomendasi_tindak_lanjut }}
                                        </div>
                                    @endif
                                    @if ($verif->foto_bukti)
                                        <div class="mt-2">
                                            <span class="font-semibold text-zinc-500">Bukti Foto Petugas:</span>
                                            <img src="{{ asset('storage/' . $verif->foto_bukti) }}" alt="Bukti Verifikasi" class="mt-1 h-24 rounded-lg object-cover border border-emerald-200">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Formulir Tindak Lanjut: 1. Penugasan Petugas Lapangan & 2. Finalisasi Status -->
                    <div class="border-t border-zinc-100 pt-4 text-xs dark:border-zinc-800">
                        @if ($aduan->status === 'selesai')
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-800/60 dark:bg-emerald-950/30">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white text-base font-bold shadow-sm" aria-hidden="true">✓</span>
                                        <div>
                                            <h4 class="font-bold text-emerald-950 dark:text-emerald-100">Tiket Aduan Telah Selesai Ditangani</h4>
                                            <p class="mt-0.5 text-[11px] text-emerald-700 dark:text-emerald-300">
                                                Aduan telah tuntas dibersihkan dan status di aplikasi warga sudah otomatis selesai.
                                                @if ($aduan->instruksi_admin)
                                                    <span class="block mt-0.5 text-zinc-600 dark:text-zinc-400 font-medium italic">Catatan akhir: "{{ $aduan->instruksi_admin }}"</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="document.getElementById('edit-forms-{{ $aduan->id }}').classList.toggle('hidden')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-zinc-900 text-emerald-800 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-zinc-800 font-semibold transition shrink-0 shadow-xs">
                                        <flux:icon.pencil-square class="size-4" />
                                        Perbarui Catatan / Buka Kembali
                                    </button>
                                </div>

                                <div id="edit-forms-{{ $aduan->id }}" class="hidden mt-4 pt-4 border-t border-emerald-200/60 dark:border-emerald-800/60">
                                    <div class="grid grid-cols-1 gap-3 2xl:grid-cols-2">
                                        <!-- Form Disposisi Petugas (SRS Butir 2) -->
                                        <form method="POST" action="{{ route('dashboard.admin.complaints.assign', $aduan) }}" class="min-w-0 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 dark:border-blue-900/60 dark:bg-blue-950/20">
                                            @csrf
                                            @method('PATCH')

                                            <div class="flex items-start gap-3">
                                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-200" aria-hidden="true"><flux:icon.user class="size-5" /></span>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-blue-950 dark:text-blue-100">Penugasan Lapangan</h4>
                                                    <p class="mt-0.5 text-[11px] leading-4 text-blue-700/80 dark:text-blue-300/80">Pilih petugas dan berikan instruksi kerja yang jelas.</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Petugas lapangan</span>
                                                    <select name="petugas_id" required class="min-h-10 w-full min-w-0 rounded-xl border border-blue-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                        <option value="">Pilih petugas</option>
                                                        @foreach ($petugasList as $p)
                                                            <option value="{{ $p->id }}" @selected($aduan->assigned_petugas_id === $p->id)>
                                                                {{ $p->name }} ({{ $p->petugasLapanganProfile->nama_wilayah_tugas ?? $p->petugasLapanganProfile->id_wilayah_rute ?? 'Armada Kota' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </label>

                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Instruksi DLH</span>
                                                    <input type="text" name="instruksi_admin" placeholder="Contoh: verifikasi dan bersihkan lokasi" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-blue-200 bg-white px-3 py-2 text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                </label>
                                            </div>

                                            <div class="mt-3 flex justify-end">
                                                <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-zinc-900">
                                                    <flux:icon.arrow-right class="size-3.5" />
                                                    {{ $aduan->assigned_petugas_id ? 'Perbarui Penugasan' : 'Tugaskan Petugas' }}
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Form Finalisasi Status Akhir (SRS Butir 3) -->
                                        <form method="POST" action="{{ route('dashboard.admin.complaints.finalize', $aduan) }}" class="min-w-0 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                                            @csrf
                                            @method('PATCH')

                                            <div class="flex items-start gap-3">
                                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-base dark:bg-emerald-900/60" aria-hidden="true">✓</span>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-emerald-950 dark:text-emerald-100">Finalisasi Aduan</h4>
                                                    <p class="mt-0.5 text-[11px] leading-4 text-emerald-700/80 dark:text-emerald-300/80">Perbarui hasil penanganan dan catatan akhir DLH.</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Status penanganan</span>
                                                    <select name="status" class="min-h-10 w-full min-w-0 rounded-xl border border-emerald-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-emerald-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                        <option value="ditindaklanjuti" {{ in_array($aduan->status, ['ditindaklanjuti', 'dalam_proses']) ? 'selected' : '' }}>Ditindaklanjuti (Proses Penanganan / Armada)</option>
                                                        <option value="selesai" {{ $aduan->status === 'selesai' ? 'selected' : '' }}>Selesai (Sudah Bersih & Tertangani)</option>
                                                        <option value="ditolak" {{ $aduan->status === 'ditolak' ? 'selected' : '' }}>Tolak Aduan (Tidak Valid / Hoax)</option>
                                                    </select>
                                                </label>

                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Catatan tindak lanjut</span>
                                                    <input type="text" name="admin_note" placeholder="Tambahkan catatan akhir" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-emerald-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                </label>
                                            </div>

                                            <div class="mt-3 flex justify-end">
                                                <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-zinc-900">
                                                    <span aria-hidden="true">✓</span>
                                                    Simpan Status Akhir
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @elseif ($aduan->status === 'ditolak')
                            <div class="rounded-2xl border border-red-200 bg-red-50/80 p-4 dark:border-red-800/60 dark:bg-red-950/30">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white text-base font-bold shadow-sm" aria-hidden="true">✕</span>
                                        <div>
                                            <h4 class="font-bold text-red-950 dark:text-red-100">Tiket Aduan Ditolak</h4>
                                            <p class="mt-0.5 text-[11px] text-red-700 dark:text-red-300">
                                                Aduan dinyatakan tidak valid atau fiktif.
                                                @if ($aduan->instruksi_admin)
                                                    <span class="block mt-0.5 text-zinc-600 dark:text-zinc-400 font-medium italic">Alasan penolakan: "{{ $aduan->instruksi_admin }}"</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="document.getElementById('edit-forms-{{ $aduan->id }}').classList.toggle('hidden')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl border border-red-300 dark:border-red-700 bg-white dark:bg-zinc-900 text-red-800 dark:text-red-200 hover:bg-red-50 dark:hover:bg-zinc-800 font-semibold transition shrink-0 shadow-xs">
                                        <flux:icon.pencil-square class="size-4" />
                                        Kelola Ulang Tiket
                                    </button>
                                </div>

                                <div id="edit-forms-{{ $aduan->id }}" class="hidden mt-4 pt-4 border-t border-red-200/60 dark:border-red-800/60">
                                    <div class="grid grid-cols-1 gap-3 2xl:grid-cols-2">
                                        <!-- Form Disposisi Petugas (SRS Butir 2) -->
                                        <form method="POST" action="{{ route('dashboard.admin.complaints.assign', $aduan) }}" class="min-w-0 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 dark:border-blue-900/60 dark:bg-blue-950/20">
                                            @csrf
                                            @method('PATCH')

                                            <div class="flex items-start gap-3">
                                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-200" aria-hidden="true"><flux:icon.user class="size-5" /></span>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-blue-950 dark:text-blue-100">Penugasan Lapangan</h4>
                                                    <p class="mt-0.5 text-[11px] leading-4 text-blue-700/80 dark:text-blue-300/80">Pilih petugas dan berikan instruksi kerja yang jelas.</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Petugas lapangan</span>
                                                    <select name="petugas_id" required class="min-h-10 w-full min-w-0 rounded-xl border border-blue-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                        <option value="">Pilih petugas</option>
                                                        @foreach ($petugasList as $p)
                                                            <option value="{{ $p->id }}" @selected($aduan->assigned_petugas_id === $p->id)>
                                                                {{ $p->name }} ({{ $p->petugasLapanganProfile->nama_wilayah_tugas ?? $p->petugasLapanganProfile->id_wilayah_rute ?? 'Armada Kota' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </label>

                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Instruksi DLH</span>
                                                    <input type="text" name="instruksi_admin" placeholder="Contoh: verifikasi dan bersihkan lokasi" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-blue-200 bg-white px-3 py-2 text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                </label>
                                            </div>

                                            <div class="mt-3 flex justify-end">
                                                <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-zinc-900">
                                                    <flux:icon.arrow-right class="size-3.5" />
                                                    {{ $aduan->assigned_petugas_id ? 'Perbarui Penugasan' : 'Tugaskan Petugas' }}
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Form Finalisasi Status Akhir (SRS Butir 3) -->
                                        <form method="POST" action="{{ route('dashboard.admin.complaints.finalize', $aduan) }}" class="min-w-0 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                                            @csrf
                                            @method('PATCH')

                                            <div class="flex items-start gap-3">
                                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-base dark:bg-emerald-900/60" aria-hidden="true">✓</span>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-emerald-950 dark:text-emerald-100">Finalisasi Aduan</h4>
                                                    <p class="mt-0.5 text-[11px] leading-4 text-emerald-700/80 dark:text-emerald-300/80">Perbarui hasil penanganan dan catatan akhir DLH.</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Status penanganan</span>
                                                    <select name="status" class="min-h-10 w-full min-w-0 rounded-xl border border-emerald-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-emerald-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                        <option value="ditindaklanjuti" {{ in_array($aduan->status, ['ditindaklanjuti', 'dalam_proses']) ? 'selected' : '' }}>Ditindaklanjuti (Proses Penanganan / Armada)</option>
                                                        <option value="selesai" {{ $aduan->status === 'selesai' ? 'selected' : '' }}>Selesai (Sudah Bersih & Tertangani)</option>
                                                        <option value="ditolak" {{ $aduan->status === 'ditolak' ? 'selected' : '' }}>Tolak Aduan (Tidak Valid / Hoax)</option>
                                                    </select>
                                                </label>

                                                <label class="min-w-0 space-y-1.5">
                                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Catatan tindak lanjut</span>
                                                    <input type="text" name="admin_note" placeholder="Tambahkan catatan akhir" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-emerald-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                </label>
                                            </div>

                                            <div class="mt-3 flex justify-end">
                                                <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-zinc-900">
                                                    <span aria-hidden="true">✓</span>
                                                    Simpan Status Akhir
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-3 2xl:grid-cols-2">
                                <!-- Form Disposisi Petugas (SRS Butir 2) -->
                                <form method="POST" action="{{ route('dashboard.admin.complaints.assign', $aduan) }}" class="min-w-0 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 dark:border-blue-900/60 dark:bg-blue-950/20">
                                    @csrf
                                    @method('PATCH')

                                    <div class="flex items-start gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-200" aria-hidden="true"><flux:icon.user class="size-5" /></span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-blue-950 dark:text-blue-100">Penugasan Lapangan</h4>
                                            <p class="mt-0.5 text-[11px] leading-4 text-blue-700/80 dark:text-blue-300/80">Pilih petugas dan berikan instruksi kerja yang jelas.</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <label class="min-w-0 space-y-1.5">
                                            <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Petugas lapangan</span>
                                            <select name="petugas_id" required class="min-h-10 w-full min-w-0 rounded-xl border border-blue-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                <option value="">Pilih petugas</option>
                                                @foreach ($petugasList as $p)
                                                    <option value="{{ $p->id }}" @selected($aduan->assigned_petugas_id === $p->id)>
                                                        {{ $p->name }} ({{ $p->petugasLapanganProfile->nama_wilayah_tugas ?? $p->petugasLapanganProfile->id_wilayah_rute ?? 'Armada Kota' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="min-w-0 space-y-1.5">
                                            <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Instruksi DLH</span>
                                            <input type="text" name="instruksi_admin" placeholder="Contoh: verifikasi dan bersihkan lokasi" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-blue-200 bg-white px-3 py-2 text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
                                        </label>
                                    </div>

                                    <div class="mt-3 flex justify-end">
                                        <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-zinc-900">
                                            <flux:icon.arrow-right class="size-3.5" />
                                            {{ $aduan->assigned_petugas_id ? 'Perbarui Penugasan' : 'Tugaskan Petugas' }}
                                        </button>
                                    </div>
                                </form>

                                <!-- Form Finalisasi Status Akhir (SRS Butir 3) -->
                                <form method="POST" action="{{ route('dashboard.admin.complaints.finalize', $aduan) }}" class="min-w-0 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                                    @csrf
                                    @method('PATCH')

                                    <div class="flex items-start gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-base dark:bg-emerald-900/60" aria-hidden="true">✓</span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-emerald-950 dark:text-emerald-100">Finalisasi Aduan</h4>
                                            <p class="mt-0.5 text-[11px] leading-4 text-emerald-700/80 dark:text-emerald-300/80">Perbarui hasil penanganan dan catatan akhir DLH.</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <label class="min-w-0 space-y-1.5">
                                            <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Status penanganan</span>
                                            <select name="status" class="min-h-10 w-full min-w-0 rounded-xl border border-emerald-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-emerald-900 dark:bg-zinc-900 dark:text-zinc-100">
                                                <option value="ditindaklanjuti" {{ in_array($aduan->status, ['ditindaklanjuti', 'dalam_proses']) ? 'selected' : '' }}>Ditindaklanjuti (Proses Penanganan / Armada)</option>
                                                <option value="selesai" {{ $aduan->status === 'selesai' ? 'selected' : '' }}>Selesai (Sudah Bersih & Tertangani)</option>
                                                <option value="ditolak" {{ $aduan->status === 'ditolak' ? 'selected' : '' }}>Tolak Aduan (Tidak Valid / Hoax)</option>
                                            </select>
                                        </label>

                                        <label class="min-w-0 space-y-1.5">
                                            <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Catatan tindak lanjut</span>
                                            <input type="text" name="admin_note" placeholder="Tambahkan catatan akhir" value="{{ $aduan->instruksi_admin }}" class="min-h-10 w-full min-w-0 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-emerald-900 dark:bg-zinc-900 dark:text-zinc-100">
                                        </label>
                                    </div>

                                    <div class="mt-3 flex justify-end">
                                        <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto dark:focus:ring-offset-zinc-900">
                                            <span aria-hidden="true">✓</span>
                                            Simpan Status Akhir
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-12 text-center text-zinc-400 text-xs">
                    Tidak ada pengaduan masyarakat yang sesuai filter.
                </div>
            @endforelse

            @if ($complaints->hasPages())
                <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700">
                    {{ $complaints->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
