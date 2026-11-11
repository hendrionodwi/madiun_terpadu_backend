<x-layouts::app :title="__('Artikel & Edukasi')">
    @php
        $fieldClass = 'mt-1 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
        $labelClass = 'text-xs font-bold text-slate-700 dark:text-slate-300';
        $categoryOptions = ['organik' => 'Organik & Kompos', 'plastik_kertas' => 'Plastik & Kertas', 'b3_rumah_tangga' => 'B3 Rumah Tangga', 'ekonomi_sirkular' => 'Ekonomi Sirkular', 'kebijakan' => 'Kebijakan Kota'];
        $statusOptions = ['draft' => 'Draf', 'scheduled' => 'Terjadwal', 'published' => 'Terbit', 'archived' => 'Arsip'];
    @endphp

    <div class="civic-page flex flex-col gap-5 p-2 md:p-4">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-civic-700 dark:text-civic-300">Konten publik</p>
                <h1 class="mt-1 font-display text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">Artikel & Edukasi</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Kelola panduan lingkungan yang tampil di portal.</p>
            </div>
            <a href="{{ route('public.education') }}" target="_blank" rel="noopener" class="civic-button-secondary self-start sm:self-auto"><flux:icon.arrow-top-right-on-square class="size-4" /> Lihat edukasi</a>
        </header>

        <x-admin.content-tabs :summary="$cmsSummary" />
        <x-admin.content-feedback />

        <div class="grid items-start gap-5 xl:grid-cols-[23rem_minmax(0,1fr)]">
            <section class="civic-card p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="civic-icon-tile"><flux:icon.book-open class="size-5" /></span>
                    <div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Artikel baru</h2><p class="text-xs text-slate-500">Tulis ringkas dan mudah dipindai.</p></div>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.articles.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div><label for="judul" class="{{ $labelClass }}">Judul</label><input id="judul" name="judul" value="{{ old('judul') }}" required maxlength="255" class="{{ $fieldClass }}" placeholder="Panduan pemilahan sampah rumah tangga"></div>
                    <div><label for="ringkasan" class="{{ $labelClass }}">Ringkasan</label><textarea id="ringkasan" name="ringkasan" rows="3" maxlength="1000" class="{{ $fieldClass }} py-3" placeholder="Gambaran singkat artikel">{{ old('ringkasan') }}</textarea></div>
                    <div><label for="isi" class="{{ $labelClass }}">Isi artikel</label><textarea id="isi" name="isi" rows="8" required class="{{ $fieldClass }} py-3" placeholder="Tulis isi panduan di sini">{{ old('isi') }}</textarea></div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div><label for="kategori" class="{{ $labelClass }}">Kategori</label><select id="kategori" name="kategori" required class="{{ $fieldClass }}">@foreach ($categoryOptions as $value => $label)<option value="{{ $value }}" @selected(old('kategori', 'organik') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div><label for="status" class="{{ $labelClass }}">Status</label><select id="status" name="status" class="{{ $fieldClass }}">@foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div><label for="penulis_nama" class="{{ $labelClass }}">Penulis</label><input id="penulis_nama" name="penulis_nama" value="{{ old('penulis_nama', auth()->user()->name) }}" maxlength="150" class="{{ $fieldClass }}"></div>
                        <div><label for="waktu_baca_menit" class="{{ $labelClass }}">Waktu baca</label><input id="waktu_baca_menit" type="number" name="waktu_baca_menit" value="{{ old('waktu_baca_menit', 4) }}" min="1" max="60" class="{{ $fieldClass }}"></div>
                    </div>
                    <div><label for="penulis_jabatan" class="{{ $labelClass }}">Jabatan penulis</label><input id="penulis_jabatan" name="penulis_jabatan" value="{{ old('penulis_jabatan', 'Tim Penyuluh DLH Kota Madiun') }}" maxlength="150" class="{{ $fieldClass }}"></div>
                    <div><label for="published_at" class="{{ $labelClass }}">Waktu terbit</label><input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="{{ $fieldClass }}"></div>
                    <div><label for="cover_image" class="{{ $labelClass }}">Sampul artikel</label><input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="{{ $fieldClass }} file:mr-3 file:border-0 file:bg-transparent file:text-xs file:font-bold file:text-civic-700"><p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WebP · maks. 5 MB</p></div>
                    <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-3 text-sm font-semibold dark:border-slate-700"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600"> Jadikan artikel unggulan</label>
                    <button type="submit" class="civic-button-primary w-full"><flux:icon.document-plus class="size-4" /> Simpan artikel</button>
                </form>
            </section>

            <section id="articleList" data-ajax-zone="admin-articles" class="civic-card overflow-hidden">
                <div class="civic-section-header">
                    <div><h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Pustaka artikel</h2><p class="text-xs text-slate-500">{{ $articles->total() }} artikel tersimpan</p></div>
                    <span class="civic-status-success">{{ $cmsSummary['published_articles'] ?? 0 }} terbit</span>
                </div>

                <form method="GET" action="{{ route('dashboard.admin.articles') }}" data-ajax-form data-ajax-targets="#articleList" class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[minmax(0,1fr)_11rem_9rem_auto] dark:border-slate-800">
                    <label class="sr-only" for="article-search">Cari artikel</label>
                    <input id="article-search" name="search" value="{{ request('search') }}" class="{{ $fieldClass }} mt-0" placeholder="Cari judul artikel">
                    <select name="kategori" aria-label="Filter kategori" class="{{ $fieldClass }} mt-0"><option value="">Semua kategori</option>@foreach ($categoryOptions as $value => $label)<option value="{{ $value }}" @selected(request('kategori') === $value)>{{ $label }}</option>@endforeach</select>
                    <select name="status" aria-label="Filter status" class="{{ $fieldClass }} mt-0"><option value="">Semua status</option>@foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
                    <button class="civic-button-secondary px-3" type="submit"><flux:icon.funnel class="size-4" /> Terapkan</button>
                </form>

                <div class="space-y-3 p-4">
                    @forelse ($articles as $article)
                        @php
                            $statusClass = match ($article->status) { 'published' => 'civic-status-success', 'scheduled' => 'civic-status-info', 'archived' => 'civic-status-danger', default => 'civic-status-warning' };
                            $statusLabel = $statusOptions[$article->status] ?? ucfirst($article->status);
                        @endphp
                        <article class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                            <div class="flex gap-4">
                                @if ($article->cover_url)
                                    <img src="{{ $article->cover_url }}" alt="" class="hidden h-24 w-32 shrink-0 rounded-xl object-cover sm:block">
                                @else
                                    <div class="hidden h-24 w-32 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700 sm:flex dark:bg-civic-950/40 dark:text-civic-300"><flux:icon.book-open class="size-7" /></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2"><span class="{{ $statusClass }}">{{ $statusLabel }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $article->kategori_label }}</span>@if ($article->is_featured)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Unggulan</span>@endif</div>
                                    <h3 class="mt-3 font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $article->judul }}</h3>
                                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $article->ringkasan ?: str($article->isi)->limit(180) }}</p>
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500"><span>{{ $article->penulis_nama }}</span><span>{{ $article->waktu_baca_menit }} menit</span><span>{{ ($article->published_at ?? $article->created_at)?->translatedFormat('d M Y') }}</span>@if ($article->status === 'published')<a href="{{ route('portal.articles.show', $article) }}" target="_blank" class="civic-link">Pratinjau <flux:icon.arrow-up-right class="size-3.5" /></a>@endif</div>
                                </div>
                            </div>

                            <details class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between px-3 text-sm font-bold text-civic-800 dark:text-civic-200">Edit artikel <flux:icon.chevron-down class="size-4" /></summary>
                                <div class="border-t border-slate-200 p-3 dark:border-slate-700">
                                    <form method="POST" action="{{ route('dashboard.admin.articles.update', $article) }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="md:col-span-2"><label class="{{ $labelClass }}">Judul</label><input name="judul" value="{{ $article->judul }}" required class="{{ $fieldClass }}"></div>
                                        <div class="md:col-span-2"><label class="{{ $labelClass }}">Ringkasan</label><textarea name="ringkasan" rows="2" class="{{ $fieldClass }} py-3">{{ $article->ringkasan }}</textarea></div>
                                        <div class="md:col-span-2"><label class="{{ $labelClass }}">Isi artikel</label><textarea name="isi" rows="7" required class="{{ $fieldClass }} py-3">{{ $article->isi }}</textarea></div>
                                        <div><label class="{{ $labelClass }}">Kategori</label><select name="kategori" class="{{ $fieldClass }}">@foreach ($categoryOptions as $value => $label)<option value="{{ $value }}" @selected($article->kategori === $value)>{{ $label }}</option>@endforeach</select></div>
                                        <div><label class="{{ $labelClass }}">Status</label><select name="status" class="{{ $fieldClass }}">@foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected($article->status === $value)>{{ $label }}</option>@endforeach</select></div>
                                        <div><label class="{{ $labelClass }}">Penulis</label><input name="penulis_nama" value="{{ $article->penulis_nama }}" class="{{ $fieldClass }}"></div>
                                        <div><label class="{{ $labelClass }}">Jabatan</label><input name="penulis_jabatan" value="{{ $article->penulis_jabatan }}" class="{{ $fieldClass }}"></div>
                                        <div><label class="{{ $labelClass }}">Waktu baca</label><input type="number" name="waktu_baca_menit" min="1" max="60" value="{{ $article->waktu_baca_menit }}" class="{{ $fieldClass }}"></div>
                                        <div><label class="{{ $labelClass }}">Waktu terbit</label><input type="datetime-local" name="published_at" value="{{ $article->published_at?->format('Y-m-d\TH:i') }}" class="{{ $fieldClass }}"></div>
                                        <div class="md:col-span-2"><label class="{{ $labelClass }}">Ganti sampul</label><input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="{{ $fieldClass }} file:mr-3 file:border-0 file:bg-transparent file:text-xs file:font-bold file:text-civic-700"></div>
                                        <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-3 text-sm font-semibold dark:border-slate-700"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked($article->is_featured) class="size-4 rounded border-slate-300 text-civic-700"> Artikel unggulan</label>
                                        <div class="md:col-span-2"><button type="submit" class="civic-button-primary">Simpan perubahan</button></div>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.admin.articles.destroy', $article) }}" class="mt-3" onsubmit="return confirm('Hapus artikel ini?')">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-bold text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/30"><flux:icon.trash class="size-4" /> Hapus artikel</button></form>
                                </div>
                            </details>
                        </article>
                    @empty
                        <div class="civic-empty">Tidak ada artikel yang cocok dengan filter.</div>
                    @endforelse
                </div>
                @if ($articles->hasPages())<div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $articles->links() }}</div>@endif
            </section>
        </div>
    </div>
</x-layouts::app>
