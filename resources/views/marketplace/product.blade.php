@extends('layouts.public', ['title' => $product->nama_produk])

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('marketplace._feedback')

        <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('public.marketplace') }}" class="font-bold text-civic-700 hover:text-civic-900">{{ __('portal.navigation.marketplace') }}</a>
            <span>/</span>
            <span class="truncate">{{ $product->nama_produk }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,0.8fr)]">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-civic-50">
                @if ($product->foto_url)
                    <img src="{{ $product->foto_url }}" alt="{{ $product->nama_produk }}" class="aspect-[4/3] h-full w-full object-cover">
                @else
                    <div class="flex aspect-[4/3] items-center justify-center text-civic-700">
                        <svg class="size-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="M8 13c4 0 6-2 7-6 2 5 0 10-5 11"/></svg>
                    </div>
                @endif
            </div>

            <div>
                <span class="inline-flex rounded-full bg-civic-100 px-3 py-1 text-xs font-extrabold text-civic-800">{{ str($product->kategori)->replace('_', ' ')->title() }}</span>
                <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950">{{ $product->nama_produk }}</h1>
                <p class="mt-3 text-3xl font-extrabold text-civic-800">Rp{{ number_format($product->harga, 0, ',', '.') }}</p>
                <p class="mt-5 text-sm leading-7 text-slate-600">{{ $product->deskripsi ?: __('portal.product.description_fallback') }}</p>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('portal.product.managed_by') }}</p>
                    <p class="mt-1 font-display text-lg font-extrabold text-slate-900">{{ $product->seller->pengelolaTps3rProfile?->nama_tps3r ?? $product->seller->name }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('portal.product.available_stock', ['count' => number_format($product->stok, 0, ',', '.')]) }}</p>
                </div>

                @auth
                    <form method="POST" action="{{ route('marketplace.cart.store', $product) }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <label class="flex min-h-12 items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700">
                            {{ __('portal.product.quantity') }}
                            <input type="number" name="jumlah" value="1" min="1" max="{{ $product->stok }}" class="w-20 rounded-lg border-slate-200 text-center focus:border-civic-600 focus:ring-civic-600">
                        </label>
                        <button type="submit" class="inline-flex min-h-12 flex-1 items-center justify-center rounded-xl bg-civic-700 px-5 text-sm font-extrabold text-white hover:bg-civic-800">{{ __('portal.common.add_to_cart') }}</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-civic-700 px-5 text-sm font-extrabold text-white hover:bg-civic-800">{{ __('portal.common.sign_in_to_buy') }}</a>
                @endauth
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <div class="mt-12">
                <h2 class="font-display text-xl font-extrabold text-slate-950">{{ __('portal.product.other_products') }}</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        <x-public.product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
