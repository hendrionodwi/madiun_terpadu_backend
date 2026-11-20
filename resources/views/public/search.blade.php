@extends('layouts.public')

@section('content')
    <section class="border-b border-slate-200 bg-[radial-gradient(circle_at_top_right,_rgba(12,120,97,0.12),_transparent_38%),linear-gradient(180deg,#ffffff_0%,#f4faf8_100%)]">
        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.search.page_kicker') }}</p>
            <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ __('portal.search.page_title') }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ __('portal.search.page_description') }}</p>

            <form method="GET" action="{{ route('public.search') }}" class="mt-7 flex flex-col gap-2 rounded-2xl border border-civic-200 bg-white p-2 shadow-sm sm:flex-row">
                <label class="flex min-h-12 flex-1 items-center px-3">
                    <svg class="size-5 shrink-0 text-civic-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $query }}"
                        maxlength="100"
                        autofocus
                        placeholder="{{ __('portal.search.placeholder') }}"
                        class="w-full border-0 bg-transparent px-3 text-sm text-slate-950 outline-none placeholder:text-slate-400"
                    >
                </label>
                <button type="submit" class="min-h-12 rounded-xl bg-civic-700 px-7 text-sm font-bold text-white transition hover:bg-civic-800 focus:outline-none focus:ring-2 focus:ring-civic-500 focus:ring-offset-2">
                    {{ __('portal.search.button') }}
                </button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($query === '')
            <div class="rounded-3xl border border-dashed border-civic-200 bg-white px-6 py-16 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-civic-50 text-civic-700">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                </span>
                <h2 class="mt-5 font-display text-xl font-extrabold text-slate-950">{{ __('portal.search.prompt_title') }}</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">{{ __('portal.search.prompt_description') }}</p>
            </div>
        @elseif ($resultCount === 0)
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3M8.5 8.5l5 5m0-5-5 5" /></svg>
                </span>
                <h2 class="mt-5 font-display text-xl font-extrabold text-slate-950">{{ __('portal.search.empty_title') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __('portal.search.empty_description') }}</p>
            </div>
        @else
            <p class="mb-7 text-sm font-semibold text-slate-600">{{ trans_choice('portal.search.summary', $resultCount, ['count' => $resultCount, 'query' => $query]) }}</p>

            <div class="grid gap-6">
                @if ($facilities->isNotEmpty())
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.search.groups.facilities') }}</h2>
                            <a href="{{ route('public.services', ['search' => $query]) }}" class="text-xs font-bold text-civic-700 hover:text-civic-900">{{ __('portal.search.view_all') }} →</a>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5">
                            @foreach ($facilities as $facility)
                                <a href="{{ route('public.facilities.show', $facility) }}" class="group rounded-2xl border border-slate-200 p-4 transition hover:border-civic-300 hover:bg-civic-50/60">
                                    <span class="text-[0.65rem] font-extrabold uppercase tracking-wider text-civic-700">{{ __('portal.search.labels.facility') }}</span>
                                    <h3 class="mt-1 line-clamp-1 font-bold text-slate-950 group-hover:text-civic-800">{{ $facility->nama_tps3r }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ collect([$facility->kelurahan, $facility->kecamatan])->filter()->join(', ') ?: $facility->alamat }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($routes->isNotEmpty())
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.search.groups.routes') }}</h2>
                            <a href="{{ route('public.services', ['search' => $query]) }}#jadwal-rute" class="text-xs font-bold text-civic-700 hover:text-civic-900">{{ __('portal.search.view_all') }} →</a>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5">
                            @foreach ($routes as $route)
                                <a href="{{ route('public.services', ['search' => $query]) }}#jadwal-rute" class="group rounded-2xl border border-slate-200 p-4 transition hover:border-civic-300 hover:bg-civic-50/60">
                                    <span class="text-[0.65rem] font-extrabold uppercase tracking-wider text-civic-700">{{ __('portal.search.labels.route') }} · {{ $route->route_code }}</span>
                                    <h3 class="mt-1 line-clamp-1 font-bold text-slate-950 group-hover:text-civic-800">{{ $route->route_name }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ collect([$route->subdistrict, $route->district, $route->end_facility])->filter()->join(' · ') }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($articles->isNotEmpty())
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.search.groups.articles') }}</h2>
                            <a href="{{ route('public.education', ['search' => $query]) }}" class="text-xs font-bold text-civic-700 hover:text-civic-900">{{ __('portal.search.view_all') }} →</a>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5">
                            @foreach ($articles as $article)
                                <a href="{{ route('portal.articles.show', $article) }}" class="group rounded-2xl border border-slate-200 p-4 transition hover:border-civic-300 hover:bg-civic-50/60">
                                    <span class="text-[0.65rem] font-extrabold uppercase tracking-wider text-civic-700">{{ __('portal.search.labels.article') }} · {{ $article->kategori_label }}</span>
                                    <h3 class="mt-1 line-clamp-2 font-bold text-slate-950 group-hover:text-civic-800">{{ $article->judul }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $article->ringkasan }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($documents->isNotEmpty())
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.search.groups.documents') }}</h2>
                            <a href="{{ route('public.education', ['search' => $query]) }}#dokumen-resmi" class="text-xs font-bold text-civic-700 hover:text-civic-900">{{ __('portal.search.view_all') }} →</a>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5">
                            @foreach ($documents as $document)
                                <a href="{{ route('portal.documents.download', $document) }}" class="group rounded-2xl border border-slate-200 p-4 transition hover:border-civic-300 hover:bg-civic-50/60">
                                    <span class="text-[0.65rem] font-extrabold uppercase tracking-wider text-civic-700">{{ __('portal.search.labels.document') }} · {{ $document->formatted_size }}</span>
                                    <h3 class="mt-1 line-clamp-2 font-bold text-slate-950 group-hover:text-civic-800">{{ $document->judul }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $document->deskripsi }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($products->isNotEmpty())
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.search.groups.products') }}</h2>
                            <a href="{{ route('public.marketplace', ['search' => $query]) }}" class="text-xs font-bold text-civic-700 hover:text-civic-900">{{ __('portal.search.view_all') }} →</a>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5">
                            @foreach ($products as $product)
                                <a href="{{ route('marketplace.products.show', $product) }}" class="group rounded-2xl border border-slate-200 p-4 transition hover:border-civic-300 hover:bg-civic-50/60">
                                    <span class="text-[0.65rem] font-extrabold uppercase tracking-wider text-civic-700">{{ __('portal.search.labels.product') }} · Rp{{ number_format($product->harga, 0, ',', '.') }}</span>
                                    <h3 class="mt-1 line-clamp-1 font-bold text-slate-950 group-hover:text-civic-800">{{ $product->nama_produk }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $product->seller->pengelolaTps3rProfile?->nama_tps3r ?? $product->seller->name }}</p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        @endif
    </section>
@endsection
