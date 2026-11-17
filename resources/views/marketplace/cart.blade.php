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
            <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="space-y-3">
                    @foreach ($items as $item)
                        <article class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="size-24 shrink-0 overflow-hidden rounded-xl bg-civic-50">
                                @if ($item['product']->foto_url)
                                    <img src="{{ $item['product']->foto_url }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('marketplace.products.show', $item['product']) }}" class="font-display font-extrabold text-slate-950 hover:text-civic-800">{{ $item['product']->nama_produk }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $item['product']->seller->pengelolaTps3rProfile?->nama_tps3r ?? $item['product']->seller->name }}</p>
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

                <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 lg:sticky lg:top-24">
                    <h2 class="font-display text-lg font-extrabold text-slate-950">Ringkasan</h2>
                    <div class="mt-4 flex items-center justify-between text-sm text-slate-600"><span>Subtotal</span><strong class="text-slate-950">Rp{{ number_format($subtotal, 0, ',', '.') }}</strong></div>
                    <p class="mt-3 border-t border-slate-100 pt-3 text-xs leading-5 text-slate-500">Ongkir dihitung sesuai pilihan pengiriman saat checkout.</p>
                    <a href="{{ route('marketplace.checkout') }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-civic-700 px-4 text-sm font-extrabold text-white hover:bg-civic-800">Lanjut checkout</a>
                </aside>
            </div>
        @endif
    </section>
@endsection
