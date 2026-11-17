@extends('layouts.public', ['title' => 'Pesanan Saya'])

@section('content')
    @php
        $tabs = [
            'semua' => 'Semua',
            'pending' => 'Belum Bayar',
            'diproses' => 'Diproses',
            'siap_dikirim' => 'Siap Dikirim',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];
        $statusLabels = [
            'pending' => 'Belum bayar',
            'dibayar' => 'Pembayaran diterima',
            'diproses' => 'Diproses',
            'siap_dikirim' => 'Siap dikirim',
            'dikirim' => 'Dalam pengiriman',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];
    @endphp

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @include('marketplace._feedback')

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">Marketplace</p>
                <h1 class="mt-1 font-display text-3xl font-extrabold text-slate-950">Pesanan saya</h1>
            </div>
            <a href="{{ route('public.marketplace') }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 hover:border-civic-600 hover:text-civic-700">Belanja lagi</a>
        </div>

        <nav class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white" aria-label="Filter status pesanan">
            <div class="flex min-w-max px-2">
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('marketplace.orders', $value === 'semua' ? [] : ['status' => $value]) }}"
                       @class([
                           'relative inline-flex min-h-12 items-center px-4 text-sm font-bold transition',
                           'text-civic-700 after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full after:bg-civic-700' => $activeStatus === $value,
                           'text-slate-500 hover:text-slate-900' => $activeStatus !== $value,
                       ])>{{ $label }}</a>
                @endforeach
            </div>
        </nav>

        <div class="mt-5 space-y-4">
            @forelse ($orders as $order)
                @php
                    $firstItem = $order->items->first();
                    $sellerName = $firstItem?->product?->seller?->pengelolaTps3rProfile?->nama_tps3r
                        ?? $firstItem?->product?->seller?->name
                        ?? 'Mitra TPS3R';
                @endphp
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3">
                        <div>
                            <p class="text-sm font-extrabold text-slate-900">{{ $sellerName }}</p>
                            <p class="font-mono text-xs text-slate-500">
                                {{ $order->nomor_pesanan }}
                                @if ($order->created_at)
                                    <span class="font-sans text-slate-400">·</span>
                                    <span class="font-sans text-slate-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                @endif
                            </p>
                        </div>
                        <span @class([
                            'rounded-full px-3 py-1 text-xs font-extrabold',
                            'bg-amber-50 text-amber-700' => in_array($order->status, ['pending', 'siap_dikirim'], true),
                            'bg-sky-50 text-sky-700' => in_array($order->status, ['dibayar', 'diproses', 'dikirim'], true),
                            'bg-emerald-50 text-emerald-700' => $order->status === 'selesai',
                            'bg-red-50 text-red-700' => $order->status === 'dibatalkan',
                        ])>{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                    </header>

                    <div class="divide-y divide-slate-100 px-5">
                        @foreach ($order->items->take(2) as $item)
                            <div class="flex gap-3 py-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-civic-50">
                                    @if ($item->product?->foto_url)
                                        <img src="{{ $item->product->foto_url }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $item->product?->nama_produk ?? 'Produk tidak tersedia' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $item->jumlah }} × Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                </div>
                                <strong class="text-sm text-slate-900">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <footer class="flex flex-wrap items-center justify-between gap-3 bg-slate-50 px-5 py-4">
                        <p class="text-sm text-slate-600">Total <strong class="ml-2 text-base text-civic-800">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</strong></p>
                        <div class="flex flex-wrap gap-2">
                            @if ($order->status === 'pending' && $order->snap_redirect_url)
                                <a href="{{ $order->snap_redirect_url }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center rounded-xl bg-amber-600 px-4 text-sm font-extrabold text-white hover:bg-amber-700">Bayar sekarang</a>
                            @endif
                            <a href="{{ route('marketplace.orders.show', $order) }}" class="inline-flex min-h-11 items-center rounded-xl bg-civic-700 px-4 text-sm font-extrabold text-white hover:bg-civic-800">Lihat detail</a>
                        </div>
                    </footer>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                    <h2 class="font-display text-xl font-extrabold text-slate-900">Belum ada pesanan</h2>
                    <p class="mt-2 text-sm text-slate-500">Produk daur ulang pilihan warga tersedia di marketplace.</p>
                    <a href="{{ route('public.marketplace') }}" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-civic-700 px-5 text-sm font-extrabold text-white">Lihat marketplace</a>
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </section>
@endsection
