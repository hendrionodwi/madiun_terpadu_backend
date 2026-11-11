<x-layouts::app :title="__('Dokumen Publik')">
    @php
        $fieldClass = 'mt-1 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $labelClass = 'text-xs font-bold text-slate-700 dark:text-slate-300';
        $categoryOptions = ['panduan' => 'Panduan Praktis', 'regulasi' => 'Regulasi & Perda', 'peta' => 'Peta & Direktori', 'laporan' => 'Laporan Kinerja', 'sop' => 'SOP Operasional'];
        $statusOptions = ['draft' => 'Draf', 'published' => 'Terbit', 'archived' => 'Arsip'];
    @endphp

    <div class="civic-page flex flex-col gap-5 p-2 md:p-4">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-civic-700 dark:text-civic-300">Konten publik</p>
                <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">Dokumen Publik</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Simpan regulasi, SOP, peta, dan laporan dalam satu pustaka.</p>
            </div>
            <a href="{{ route('public.education') }}#dokumen-publik" target="_blank" rel="noopener" class="civic-button-secondary self-start sm:self-auto"><flux:icon.arrow-top-right-on-square class="size-4" /> Lihat pustaka</a>
        </header>

        <x-admin.content-tabs :summary="$cmsSummary" />
        <x-admin.content-feedback />

        <div class="grid items-start gap-5 xl:grid-cols-[22rem_minmax(0,1fr)]">
            <section class="civic-card p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="civic-icon-tile"><flux:icon.document-arrow-up class="size-5" /></span>
                    <div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Unggah dokumen</h2><p class="text-xs text-slate-500">PDF, DOC, atau DOCX.</p></div>
                </div>
                <form method="POST" action="{{ route('dashboard.admin.documents.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div><label for="judul" class="{{ $labelClass }}">Judul dokumen</label><input id="judul" name="judul" value="{{ old('judul') }}" required maxlength="255" class="{{ $fieldClass }}" placeholder="SOP penjemputan sampah terpilah"></div>
                    <div><label for="deskripsi" class="{{ $labelClass }}">Deskripsi</label><textarea id="deskripsi" name="deskripsi" rows="4" maxlength="1000" class="{{ $fieldClass }} py-3" placeholder="Jelaskan isi dan tujuan dokumen">{{ old('deskripsi') }}</textarea></div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div><label for="kategori" class="{{ $labelClass }}">Kategori</label><select id="kategori" name="kategori" required class="{{ $fieldClass }}">@foreach ($categoryOptions as $value => $label)<option value="{{ $value }}" @selected(old('kategori', 'panduan') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div><label for="status" class="{{ $labelClass }}">Status</label><select id="status" name="status" class="{{ $fieldClass }}">@foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div><label for="tanggal_rilis" class="{{ $labelClass }}">Tanggal rilis</label><input id="tanggal_rilis" type="date" name="tanggal_rilis" value="{{ old('tanggal_rilis', now()->toDateString()) }}" class="{{ $fieldClass }}"></div>
                        <div><label for="hak_akses_label" class="{{ $labelClass }}">Label akses</label><input id="hak_akses_label" name="hak_akses_label" value="{{ old('hak_akses_label', 'Akses Bebas Publik') }}" maxlength="100" class="{{ $fieldClass }}"></div>
                    </div>
                    <div><label for="file_dokumen" class="{{ $labelClass }}">File dokumen</label><input id="file_dokumen" type="file" name="file_dokumen" accept=".pdf,.doc,.docx" required class="{{ $fieldClass }} file:mr-3 file:border-0 file:bg-transparent file:text-xs file:font-bold file:text-civic-700"><p class="mt-1 text-xs text-slate-500">Ukuran maksimal 10 MB</p></div>
                    <button type="submit" class="civic-button-primary w-full"><flux:icon.cloud-arrow-up class="size-4" /> Unggah dokumen</button>
                </form>
            </section>

            <section id="documentList" data-ajax-zone="admin-documents" class="civic-card overflow-hidden">
                <div class="civic-section-header">
                    <div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Pustaka dokumen</h2><p class="text-xs text-slate-500">{{ $documents->total() }} dokumen tersimpan</p></div>
                    <span class="civic-status-success">{{ $cmsSummary['published_documents'] ?? 0 }} terbit</span>
                </div>

                <form method="GET" action="{{ route('dashboard.admin.documents') }}" data-ajax-form data-ajax-targets="#documentList" class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[minmax(0,1fr)_11rem_9rem_auto] dark:border-slate-800">
                    <label class="sr-only" for="document-search">Cari dokumen</label><input id="document-search" name="search" value="{{ request('search') }}" class="{{ $fieldClass }} mt-0" placeholder="Cari judul dokumen">
                    <select name="kategori" aria-label="Filter kategori" class="{{ $fieldClass }} mt-0"><option value="">Semua kategori</option>@foreach ($categoryOptions as $value => $label)<option value="{{ $value }}" @selected(request('kategori') === $value)>{{ $label }}</option>@endforeach</select>
                    <select name="status" aria-label="Filter status" class="{{ $fieldClass }} mt-0"><option value="">Semua status</option>@foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
                    <button class="civic-button-secondary px-3" type="submit"><flux:icon.funnel class="size-4" /> Terapkan</button>
                </form>

                <div class="grid gap-3 p-4 lg:grid-cols-2">
                    @forelse ($documents as $document)
                        @php
                            $statusClass = match ($document->status) { 'published' => 'civic-status-success', 'archived' => 'civic-status-danger', default => 'civic-status-warning' };
                            $statusLabel = $statusOptions[$document->status] ?? ucfirst($document->status);
                        @endphp
                        <article class="flex flex-col rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                            <div class="flex items-start gap-3">
                                <span class="civic-icon-tile"><flux:icon.document-text class="size-5" /></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2"><span class="{{ $statusClass }}">{{ $statusLabel }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $document->kategori_label }}</span></div>
                                    <h3 class="mt-3 font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $document->judul }}</h3>
                                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $document->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800"><span>{{ $document->file_format }} · {{ $document->formatted_size }}</span><span>{{ $document->tanggal_rilis?->translatedFormat('d M Y') }}</span><span>{{ number_format($document->download_count, 0, ',', '.') }} unduhan</span></div>
                            @if ($document->status === 'published')<a href="{{ route('portal.documents.download', $document) }}" target="_blank" class="civic-button-secondary mt-3 w-full text-xs"><flux:icon.arrow-down-tray class="size-4" /> Buka dokumen</a>@endif

                            <details class="mt-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between px-3 text-sm font-bold text-civic-800 dark:text-civic-200">Edit dokumen <flux:icon.chevron-down class="size-4" /></summary>
                                <div class="border-t border-slate-200 p-3 dark:border-slate-700">
                                    <form method="POST" action="{{ route('dashboard.admin.documents.update', $document) }}" enctype="multipart/form-data" class="space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <div><label class="{{ $labelClass }}">Judul</label><input name="judul" value="{{ $document->judul }}" required class="{{ $fieldClass }}"></div>
                                        <div><label class="{{ $labelClass }}">Deskripsi</label><textarea name="deskripsi" rows="3" class="{{ $fieldClass }} py-3">{{ $document->deskripsi }}</textarea></div>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <div><label class="{{ $labelClass }}">Kategori</label><select name="kategori" class="{{ $fieldClass }}">@foreach ($categoryOptions as $value => $label)<option value="{{ $value }}" @selected($document->kategori === $value)>{{ $label }}</option>@endforeach</select></div>
                                            <div><label class="{{ $labelClass }}">Status</label><select name="status" class="{{ $fieldClass }}">@foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected($document->status === $value)>{{ $label }}</option>@endforeach</select></div>
                                            <div><label class="{{ $labelClass }}">Tanggal rilis</label><input type="date" name="tanggal_rilis" value="{{ $document->tanggal_rilis?->format('Y-m-d') }}" class="{{ $fieldClass }}"></div>
                                            <div><label class="{{ $labelClass }}">Label akses</label><input name="hak_akses_label" value="{{ $document->hak_akses_label }}" class="{{ $fieldClass }}"></div>
                                        </div>
                                        <div><label class="{{ $labelClass }}">Ganti file</label><input type="file" name="file_dokumen" accept=".pdf,.doc,.docx" class="{{ $fieldClass }} file:mr-3 file:border-0 file:bg-transparent file:text-xs file:font-bold file:text-civic-700"></div>
                                        <button type="submit" class="civic-button-primary">Simpan perubahan</button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.admin.documents.destroy', $document) }}" class="mt-3" onsubmit="return confirm('Hapus dokumen ini?')">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-bold text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/30"><flux:icon.trash class="size-4" /> Hapus dokumen</button></form>
                                </div>
                            </details>
                        </article>
                    @empty
                        <div class="civic-empty lg:col-span-2">Tidak ada dokumen yang cocok dengan filter.</div>
                    @endforelse
                </div>
                @if ($documents->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $documents->links() }}</div>@endif
            </section>
        </div>
    </div>
</x-layouts::app>
