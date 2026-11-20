@extends('layouts.public', ['title' => isset($article) ? $article->judul : __('portal.education.title')])

@section('content')
    @if (isset($article))
        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8 portal-settle">
                <a href="{{ route('public.education') }}" class="inline-flex items-center gap-2 text-xs font-bold text-civic-700 hover:text-civic-900">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
                    {{ __('portal.education.back') }}
                </a>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-civic-100 px-3 py-1 font-bold text-civic-800">{{ $article->kategori_label }}</span>
                    <span class="text-slate-500 tabular-nums">{{ __('portal.common.read_minutes', ['count' => $article->waktu_baca_menit]) }}</span>
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
                    {{ __('portal.education.written_by', ['name' => $article->penulis_nama ?: __('portal.education.default_author')]) }}{{ $article->penulis_jabatan ? ' · '.$article->penulis_jabatan : '' }}
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
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.education.kicker') }}</p>
                    <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">{{ __('portal.education.heading') }}</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">{{ __('portal.education.description') }}</p>
                </div>
                <div class="grid grid-cols-3 gap-3 portal-settle-delay-1">
                    @foreach ([
                        [__('portal.education.types.organic'), __('portal.education.types.organic_copy'), 'border-emerald-200 bg-emerald-50/70 text-emerald-900'],
                        [__('portal.education.types.inorganic'), __('portal.education.types.inorganic_copy'), 'border-sky-200 bg-sky-50/70 text-sky-900'],
                        [__('portal.education.types.residue'), __('portal.education.types.residue_copy'), 'border-amber-200 bg-amber-50/70 text-amber-900'],
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
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.education.master_kicker') }}</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ __('portal.education.categories') }}</h2>
        </div>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($categories as $category)
                <article class="portal-interactive-card rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="rounded-full bg-civic-100 px-2.5 py-1 text-xs font-bold text-civic-800">{{ str($category->pillar ?: 'kategori')->replace('_', ' ')->title() }}</span>
                        <span class="font-mono text-xs font-bold text-slate-400">{{ strtoupper($category->code) }}</span>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-extrabold text-slate-950">{{ $category->name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $category->description ?: __('portal.education.category_fallback') }}</p>
                </article>
            @empty
                <x-public.empty-state class="sm:col-span-2 lg:col-span-3" :title="__('portal.education.category_empty_title')" :description="__('portal.education.category_empty_description')" />
            @endforelse
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.education.publication_kicker') }}</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ __('portal.education.latest_articles') }}</h2>
                </div>
            </div>
            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($articles as $item)
                    @php
                        $cover = $item->cover_url ?: $item->thumbnail_url;
                    @endphp
                    <a href="{{ route('portal.articles.show', $item) }}" class="portal-interactive-card group flex h-full flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-civic-canvas">
                        <div>
                            @if ($cover)
                                <div class="aspect-video w-full overflow-hidden bg-slate-100">
                                    <img src="{{ $cover }}" alt="{{ $item->judul }}" class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.03]" loading="lazy">
                                </div>
                            @else
                                <div class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-civic-50 to-civic-100 text-civic-400">
                                    <flux:icon.book-open class="size-10 text-civic-500" />
                                </div>
                            @endif
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <span class="rounded-full bg-civic-100 px-2.5 py-1 font-bold text-civic-800">{{ $item->kategori_label }}</span>
                                    <span class="text-slate-500 tabular-nums">{{ $item->waktu_baca_menit }} {{ __('portal.common.minutes') }}</span>
                                </div>
                                <h3 class="mt-4 line-clamp-2 min-h-[3rem] font-display text-lg font-extrabold leading-6 text-slate-950 group-hover:text-civic-800">{{ $item->judul }}</h3>
                                <p class="mt-2 line-clamp-3 min-h-[4.5rem] text-sm leading-6 text-slate-600">{{ $item->ringkasan ?: __('portal.education.article_fallback') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200/70 px-5 py-3 text-xs font-semibold text-slate-500">
                            <span>{{ $item->penulis_nama }} · {{ $item->published_at?->translatedFormat('d M Y') }}</span>
                            <span class="font-bold text-civic-700">{{ __('portal.common.read') }} →</span>
                        </div>
                    </a>
                @empty
                    <x-public.empty-state class="md:col-span-2 xl:col-span-3" :title="__('portal.education.article_empty_title')" :description="__('portal.education.article_empty_description')" />
                @endforelse
            </div>
        </div>
    </section>

    <section class="border-b border-civic-200/70 bg-civic-50/70">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.education.workflow_kicker') }}</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ __('portal.education.workflow_title') }}</h2>
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
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.education.facility_kicker') }}</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ __('portal.education.registered_facilities') }}</h2>
            </div>
            <a href="{{ route('public.services') }}" class="text-sm font-bold text-civic-700 hover:text-civic-900">{{ __('portal.education.open_map') }}</a>
        </div>
        <div class="mt-8 grid gap-4 {{ $facilities->count() <= 2 ? 'md:grid-cols-2' : 'md:grid-cols-3' }}">
            @forelse ($facilities as $facility)
                <a href="{{ route('public.facilities.show', $facility) }}" class="portal-interactive-card rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="font-mono text-xs font-bold uppercase tracking-wider text-civic-700">{{ $facility->kode_tps3r ?: 'TPS3R' }}</p>
                    <h3 class="mt-2 font-display text-lg font-extrabold text-slate-950">{{ $facility->nama_tps3r }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $facility->kelurahan }}{{ $facility->kecamatan ? ', '.__('portal.common.district_short').' '.$facility->kecamatan : '' }}</p>
                </a>
            @empty
                <x-public.empty-state class="md:col-span-3" :title="__('portal.education.facility_empty_title')" :description="__('portal.education.facility_empty_description')" />
            @endforelse
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8" data-scroll-fade>
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.education.document_kicker') }}</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ __('portal.education.documents_title') }}</h2>
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
                            <span class="font-medium text-slate-500 tabular-nums">{{ strtoupper($document->file_format ?: 'PDF') }} · {{ $document->formatted_size }} · {{ __('portal.common.downloads', ['count' => number_format((int) $document->download_count, 0, ',', '.')]) }}</span>
                        </div>
                        <h3 class="mt-2 font-display font-extrabold text-slate-950">{{ $document->judul }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $document->deskripsi }}</p>
                    </div>
                    <a href="{{ route('portal.documents.download', $document) }}" class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 hover:border-civic-700 hover:bg-civic-700 hover:text-white">
                        {{ __('portal.common.download_document') }}
                    </a>
                </div>
            @empty
                <x-public.empty-state :title="__('portal.education.document_empty_title')" :description="__('portal.education.document_empty_description')" />
            @endforelse
        </div>
    </section>

    <x-public.action-band />
@endsection
