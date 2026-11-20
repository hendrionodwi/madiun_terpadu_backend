@extends('layouts.public', ['title' => 'Keranjang Belanja'])

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @include('marketplace._feedback')

        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Marketplace</p>
                <h1 class="mt-1 font-display text-3xl font-extrabold text-slate-950">Keranjang Belanja</h1>
            </div>
            <a href="{{ route('marketplace.orders') }}" class="text-sm font-bold text-civic-700 hover:text-civic-900">Pesanan saya →</a>
        </div>

        @if ($items->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <p class="font-display text-xl font-extrabold text-slate-900">Keranjang masih kosong</p>
                <p class="mt-2 text-sm text-slate-500">Pilih produk dari katalog TPS3R.</p>
                <a href="{{ route('public.marketplace') }}" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-civic-700 px-5 text-sm font-bold text-white">Lihat katalog</a>
            </div>
        @else
            @if ($groups->count() > 1)
                <div class="mt-6 rounded-2xl border border-civic-200 bg-civic-50 px-4 py-3 text-sm text-civic-900">
                    Produk dikelompokkan per TPS3R. Checkout dilakukan satu toko per pesanan agar ongkir dan pembayaran tetap akurat.
                </div>
            @endif

            <div class="mt-6 space-y-5">
                @foreach ($groups as $group)
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-5 py-4">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-civic-700">TPS3R Penjual</p>
                                <h2 class="mt-1 font-display text-lg font-extrabold text-slate-950">{{ $group['seller_name'] }}</h2>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600">{{ $group['items']->count() }} produk</span>
                        </header>

                        <div class="divide-y divide-slate-100">
                            @foreach ($group['items'] as $item)
                                <article class="flex gap-4 p-4 sm:p-5">
                                    <div class="size-24 shrink-0 overflow-hidden rounded-xl bg-civic-50">
                                        @if ($item['product']->foto_url)
                                            <img src="{{ $item['product']->foto_url }}" alt="" class="h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('marketplace.products.show', $item['product']) }}" class="font-display font-extrabold text-slate-950 hover:text-civic-800">{{ $item['product']->nama_produk }}</a>
                                        <p class="mt-2 font-bold text-civic-800">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <form method="POST" action="{{ route('marketplace.cart.update', $item['product']) }}" class="flex items-center gap-2">
                                                @csrf @method('PATCH')
                                                <input type="number" name="jumlah" value="{{ $item['jumlah'] }}" min="1" max="{{ $item['product']->stok }}" class="h-10 w-20 rounded-lg border-slate-200 text-center text-sm focus:border-civic-600 focus:ring-civic-600">
                                                <button class="h-10 rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-700 hover:bg-slate-50">Perbarui</button>
                                            </form>
                                            <form method="POST" action="{{ route('marketplace.cart.destroy', $item['product']) }}">
                                                @csrf @method('DELETE')
                                                <button class="h-10 px-2 text-xs font-bold text-red-700">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <footer class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-600">Subtotal toko <strong class="ml-1 text-slate-950">Rp{{ number_format($group['subtotal'], 0, ',', '.') }}</strong></p>
                            <a href="{{ route('marketplace.checkout', ['seller_id' => $group['seller_id']]) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-civic-700 px-5 text-sm font-extrabold text-white hover:bg-civic-800">Checkout toko ini</a>
                        </footer>
                    </section>
                @endforeach

                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">
                    <span>Total seluruh keranjang</span>
                    <strong class="text-base text-slate-950">Rp{{ number_format($subtotal, 0, ',', '.') }}</strong>
                </div>
            </div>
        @endif
    </section>
@endsection
