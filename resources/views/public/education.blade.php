@extends('layouts.public', ['title' => isset($article) ? $article->judul : 'Edukasi & Pengolahan'])

@section('content')
    @if (isset($article))
        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8 portal-settle">
                <a href="{{ route('public.education') }}" class="inline-flex items-center gap-2 text-xs font-bold text-civic-700 hover:text-civic-900">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
                    Kembali ke pusat edukasi
                </a>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-civic-100 px-3 py-1 font-bold text-civic-800">{{ $article->kategori_label }}</span>
                    <span class="text-slate-500 tabular-nums">{{ $article->waktu_baca_menit }} menit baca</span>
                    @if ($article->published_at)
                        <span class="text-slate-400">·</span>
                        <span class="text-slate-500 tabular-nums">{{ $article->published_at->translatedFormat('d M Y') }}</span>
                    @endif
                </div>
                <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ $article->judul }}</h1>
                @if ($article->ringkasan)
                    <p class="mt-3 text-base leading-7 text-slate-600">{{ $article->ringkasan }}</p>
                @endif
                <p class="mt-4 text-xs font-semibold text-slate-500">
                    Ditulis oleh {{ $article->penulis_nama ?: 'Tim Edukasi DLH Kota Madiun' }}{{ $article->penulis_jabatan ? ' · '.$article->penulis_jabatan : '' }}
                </p>

                @if ($article->cover_url)
                    <img src="{{ $article->cover_url }}" alt="{{ $article->judul }}" class="mt-6 max-h-96 w-full rounded-2xl border border-slate-200 object-cover">
                @endif

                <div class="mt-8 space-y-4 rounded-2xl border border-slate-200 bg-civic-canvas p-6 text-sm leading-7 text-slate-800 whitespace-pre-line sm:p-8 sm:text-base">
                    {{ $article->isi }}
                </div>
            </div>
        </section>
    @else
        <section class="border-b border-slate-200 bg-gradient-to-b from-civic-50/80 via-white to-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-8 portal-settle">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Panduan warga</p>
                    <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">Pilah lebih tepat, olah lebih banyak.</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Kenali kategori sampah, alur penjemputan, dan dokumen panduan resmi DLH sebelum menyerahkan sampah ke petugas atau TPS3R.</p>
                </div>
                <div class="grid grid-cols-3 gap-3 portal-settle-delay-1">
                    @foreach ([
                        ['Organik', 'Daun & sisa dapur', 'border-emerald-200 bg-emerald-50/70 text-emerald-900'],
                        ['Anorganik', 'Plastik, kertas, logam', 'border-sky-200 bg-sky-50/70 text-sky-900'],
                        ['Residu', 'Tidak dapat didaur ulang', 'border-amber-200 bg-amber-50/70 text-amber-900'],
                    ] as [$label, $copy, $toneClass])
                        <div class="rounded-2xl border {{ $toneClass }} p-4 text-center">
                            <span class="mx-auto flex size-9 items-center justify-center rounded-lg bg-white/90 text-civic-700 shadow-xs">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path d="M8 13c4 0 6-2 7-6 2 5 0 10-5 11" /></svg>
                            </span>
                            <p class="mt-3 text-xs font-extrabold sm:text-sm">{{ $label }}</p>
                            <p class="mt-1 hidden text-xs leading-5 opacity-80 sm:block">{{ $copy }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8" data-scroll-fade>
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Data master aktif</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">Kategori sampah</h2>
        </div>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($categories as $category)
                <article class="portal-interactive-card rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="rounded-full bg-civic-100 px-2.5 py-1 text-xs font-bold text-civic-800">{{ str($category->pillar ?: 'kategori')->replace('_', ' ')->title() }}</span>
                        <span class="font-mono text-xs font-bold text-slate-400">{{ strtoupper($category->code) }}</span>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-extrabold text-slate-950">{{ $category->name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $category->description ?: 'Ikuti ketentuan pemilahan dari petugas fasilitas.' }}</p>
                </article>
            @empty
                <x-public.empty-state class="sm:col-span-2 lg:col-span-3" title="Kategori belum tersedia" description="Admin DLH dapat menambahkan kategori melalui Data Master." />
            @endforelse
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Publikasi DLH</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">Artikel & panduan terbaru</h2>
                </div>
            </div>
            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($articles as $item)
                    <a href="{{ route('portal.articles.show', $item) }}" class="portal-interactive-card group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-civic-canvas">
                        <div>
                            @if ($item->cover_url)
                                <div class="overflow-hidden bg-slate-100">
                                    <img src="{{ $item->cover_url }}" alt="{{ $item->judul }}" class="h-44 w-full object-cover transition duration-500 ease-out group-hover:scale-[1.03]">
                                </div>
                            @endif
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <span class="rounded-full bg-civic-100 px-2.5 py-1 font-bold text-civic-800">{{ $item->kategori_label }}</span>
                                    <span class="text-slate-500 tabular-nums">{{ $item->waktu_baca_menit }} menit</span>
                                </div>
                                <h3 class="mt-4 font-display text-lg font-extrabold leading-6 text-slate-950 group-hover:text-civic-800">{{ $item->judul }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{{ $item->ringkasan }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200/70 px-5 py-3 text-xs font-semibold text-slate-500">
                            <span>{{ $item->penulis_nama }} · {{ $item->published_at?->translatedFormat('d M Y') }}</span>
                            <span class="font-bold text-civic-700">Baca →</span>
                        </div>
                    </a>
                @empty
                    <x-public.empty-state class="md:col-span-2 xl:col-span-3" title="Artikel belum tersedia" description="Admin DLH dapat menerbitkan artikel melalui manajemen konten portal." />
                @endforelse
            </div>
        </div>
    </section>

    <section class="border-b border-civic-200/70 bg-civic-50/70">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Alur layanan</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">Dari rumah ke produk baru</h2>
            <ol class="mt-8 grid gap-4 md:grid-cols-5">
                @foreach ($serviceSteps as $step)
                    <li class="rounded-2xl border border-civic-200/70 bg-white p-5">
                        <span class="flex size-8 items-center justify-center rounded-lg bg-civic-700 text-xs font-extrabold text-white tabular-nums">{{ $step['step'] }}</span>
                        <h3 class="mt-4 font-display font-extrabold text-slate-950">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step['description'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8" data-scroll-fade>
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Tempat pengolahan</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">Fasilitas terdaftar</h2>
            </div>
            <a href="{{ route('public.services') }}" class="text-sm font-bold text-civic-700 hover:text-civic-900">Buka peta →</a>
        </div>
        <div class="mt-8 grid gap-4 {{ $facilities->count() <= 2 ? 'md:grid-cols-2' : 'md:grid-cols-3' }}">
            @forelse ($facilities as $facility)
                <a href="{{ route('public.services', array_filter(['district' => $facility->kecamatan, 'search' => $facility->nama_tps3r])) }}" class="portal-interactive-card rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="font-mono text-xs font-bold uppercase tracking-wider text-civic-700">{{ $facility->kode_tps3r ?: 'TPS3R' }}</p>
                    <h3 class="mt-2 font-display text-lg font-extrabold text-slate-950">{{ $facility->nama_tps3r }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $facility->kelurahan }}{{ $facility->kecamatan ? ', Kec. '.$facility->kecamatan : '' }}</p>
                </a>
            @empty
                <x-public.empty-state class="md:col-span-3" title="Fasilitas belum tersedia" description="Profil TPS3R aktif akan ditampilkan otomatis." />
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8" data-scroll-fade>
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Dokumen publik</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">Panduan & regulasi</h2>
        </div>
        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @forelse ($documents as $document)
                <div class="flex flex-col gap-4 border-b border-slate-100 p-5 transition-colors duration-150 last:border-b-0 hover:bg-slate-50/70 sm:flex-row sm:items-center">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 2h9l5 5v15H6z" /><path d="M14 2v6h6M9 13h6M9 17h4" /></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-civic-100 px-2.5 py-0.5 font-bold text-civic-800">{{ $document->kategori_label }}</span>
                            <span class="font-medium text-slate-500 tabular-nums">{{ strtoupper($document->file_format ?: 'PDF') }} · {{ $document->formatted_size }} · {{ number_format((int) $document->download_count, 0, ',', '.') }} unduhan</span>
                        </div>
                        <h3 class="mt-2 font-display font-extrabold text-slate-950">{{ $document->judul }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $document->deskripsi }}</p>
                    </div>
                    <a href="{{ route('portal.documents.download', $document) }}" class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 hover:border-civic-700 hover:bg-civic-700 hover:text-white">
                        Unduh dokumen
                    </a>
                </div>
            @empty
                <x-public.empty-state title="Dokumen belum tersedia" description="Dokumen resmi akan tampil setelah diterbitkan Admin DLH." />
            @endforelse
        </div>
    </section>

    <x-public.action-band />
@endsection
