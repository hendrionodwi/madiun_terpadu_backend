@extends('layouts.public', ['title' => 'Detail Pesanan'])

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Menunggu pembayaran',
            'dibayar' => 'Pembayaran diterima',
            'diproses' => 'Sedang diproses',
            'siap_dikirim' => 'Siap dikirim',
            'dikirim' => 'Dalam pengiriman',
            'selesai' => 'Pesanan selesai',
            'dibatalkan' => 'Pesanan dibatalkan',
        ];
        $canCancel = in_array($order->status, ['pending', 'dibayar', 'diproses'], true)
            && ! ($order->metode_pembayaran === 'midtrans' && $order->status !== 'pending');
    @endphp

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @include('marketplace._feedback')

        <a href="{{ route('marketplace.orders') }}" class="text-sm font-bold text-civic-700 hover:text-civic-900">← Kembali ke pesanan</a>
        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="font-mono text-sm text-slate-500">{{ $order->nomor_pesanan }}</p>
                <h1 class="mt-1 font-display text-3xl font-extrabold text-slate-950">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</h1>
                <p class="mt-1 text-sm text-slate-500">Dibuat {{ $order->created_at?->translatedFormat('d M Y, H:i') }} WIB</p>
            </div>
            @if ($order->status === 'pending' && $order->snap_redirect_url)
                <a href="{{ $order->snap_redirect_url }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center rounded-xl bg-amber-600 px-5 text-sm font-extrabold text-white hover:bg-amber-700">Lanjutkan pembayaran</a>
            @endif
        </div>

        <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-5">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <h2 class="border-b border-slate-100 px-5 py-4 font-display text-lg font-extrabold text-slate-950">Produk</h2>
                    <div class="divide-y divide-slate-100 px-5">
                        @foreach ($order->items as $item)
                            <div class="flex gap-4 py-4">
                                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-civic-50">
                                    @if ($item->product?->foto_url)
                                        <img src="{{ $item->product->foto_url }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-slate-900">{{ $item->product?->nama_produk ?? 'Produk tidak tersedia' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item->jumlah }} × Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                </div>
                                <strong class="text-sm">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-display text-lg font-extrabold text-slate-950">Pengiriman</h2>
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">Metode</dt><dd class="mt-1 font-bold text-slate-900">{{ $order->metode_pengiriman === 'diantar' ? 'Diantar ke alamat' : 'Ambil mandiri di TPS3R' }}</dd></div>
                        <div><dt class="text-slate-500">Biaya</dt><dd class="mt-1 font-bold text-slate-900">{{ $order->biaya_pengiriman > 0 ? 'Rp'.number_format($order->biaya_pengiriman, 0, ',', '.') : 'Gratis' }}</dd></div>
                        @if ($order->shipping_source === 'local_tps3r')
                            <div><dt class="text-slate-500">Layanan</dt><dd class="mt-1 font-bold text-slate-900">Armada TPS3R</dd></div>
                            <div><dt class="text-slate-500">Wilayah</dt><dd class="mt-1 font-bold text-slate-900">{{ $order->destination_kelurahan }}, {{ $order->destination_kecamatan }}</dd></div>
                        @elseif ($order->shipping_source === 'rajaongkir')
                            <div><dt class="text-slate-500">Kurir</dt><dd class="mt-1 font-bold uppercase text-slate-900">{{ $order->shipping_courier }} {{ $order->shipping_service }}</dd></div>
                            <div><dt class="text-slate-500">Estimasi</dt><dd class="mt-1 font-bold text-slate-900">{{ $order->shipping_etd ?: 'Mengikuti kurir' }}</dd></div>
                        @endif
                        @if ($order->alamat_pengiriman)
                            <div class="sm:col-span-2"><dt class="text-slate-500">Alamat</dt><dd class="mt-1 font-bold text-slate-900">{{ $order->alamat_pengiriman }}</dd></div>
                        @endif
                    </dl>
                </section>

                @if ($order->status === 'dikirim')
                    <form method="POST" action="{{ route('marketplace.orders.confirm', $order) }}" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        @csrf
                        @method('PATCH')
                        <h2 class="font-display text-lg font-extrabold text-emerald-950">Pesanan sudah diterima?</h2>
                        <p class="mt-1 text-sm text-emerald-800">Pastikan jumlah dan kondisi produk sesuai sebelum konfirmasi.</p>
                        <button class="mt-4 min-h-11 rounded-xl bg-civic-700 px-5 text-sm font-extrabold text-white">Konfirmasi diterima</button>
                    </form>
                @endif

                @if ($canCancel)
                    <form method="POST" action="{{ route('marketplace.orders.cancel', $order) }}" class="rounded-2xl border border-red-200 bg-white p-5">
                        @csrf
                        @method('PATCH')
                        <label class="block text-sm font-bold text-slate-700">Alasan pembatalan</label>
                        <textarea name="alasan" rows="2" required minlength="5" maxlength="300" class="mt-2 w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500"></textarea>
                        <button class="mt-3 min-h-11 rounded-xl border border-red-200 px-5 text-sm font-extrabold text-red-700 hover:bg-red-50">Batalkan pesanan</button>
                    </form>
                @endif
            </div>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 lg:sticky lg:top-24">
                <h2 class="font-display text-lg font-extrabold text-slate-950">Ringkasan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Subtotal</dt><dd class="font-bold">Rp{{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Pengiriman</dt><dd class="font-bold">Rp{{ number_format($order->biaya_pengiriman, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-3 text-base"><dt class="font-extrabold">Total</dt><dd class="font-extrabold text-civic-800">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Pembayaran</dt><dd class="text-right font-bold">{{ $order->metode_pembayaran === 'poin_sampah' ? 'Poin warga' : 'Midtrans' }}</dd></div>
                </dl>
            </aside>
        </div>
    </section>
@endsection
