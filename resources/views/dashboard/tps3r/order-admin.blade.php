<x-layouts::app :title="__('Detail Monitoring Pesanan')">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-2 md:p-4">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div class="flex flex-col gap-1">
                <p class="text-sm font-semibold text-teal-700 dark:text-teal-400">Monitoring Marketplace se-Kota</p>
                <h1 class="font-mono text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $order->nomor_pesanan }}</h1>
                <p class="text-xs text-zinc-500">Dibuat pada {{ $order->created_at->format('d M Y, H:i:s') }}</p>
            </div>
            <a href="{{ route('dashboard.tps3r.orders') }}" class="inline-flex w-fit items-center gap-1.5 rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                &larr; Kembali ke monitoring pesanan
            </a>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Pembeli</p>
                <p class="mt-2 font-bold text-zinc-900 dark:text-zinc-100">{{ $order->buyer->name }}</p>
                <p class="text-xs text-zinc-500">{{ $order->buyer->phone_number ?: $order->buyer->email }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Status Pesanan</p>
                <p class="mt-2 font-bold capitalize text-zinc-900 dark:text-zinc-100">{{ str_replace('_', ' ', $order->status) }}</p>
                <p class="text-xs text-zinc-500">Status agregat seluruh TPS3R</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Metode Pembayaran</p>
                <p class="mt-2 font-bold capitalize text-zinc-900 dark:text-zinc-100">{{ str_replace('_', ' ', $order->metode_pembayaran) }}</p>
                <p class="text-xs text-zinc-500">{{ $order->paid_at ? 'Lunas '.$order->paid_at->format('d M Y, H:i') : 'Belum tercatat lunas' }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total Pesanan</p>
                <p class="mt-2 text-xl font-black text-teal-700 dark:text-teal-400">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                <p class="text-xs text-zinc-500">{{ $order->fulfillments->count() }} fulfillment TPS3R</p>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Rincian Fulfillment per TPS3R</h2>
                <p class="text-sm text-zinc-500">Setiap status dan subtotal ditampilkan terpisah agar transaksi multi-penjual dapat dipantau dengan tepat.</p>
            </div>

            @forelse ($order->fulfillments as $fulfillment)
                @php
                    $sellerItems = $order->items->filter(
                        fn ($item) => $item->product?->seller_id === $fulfillment->seller_id,
                    );
                    $badgeClass = match ($fulfillment->status) {
                        \App\Enums\OrderFulfillmentStatus::Menunggu => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                        \App\Enums\OrderFulfillmentStatus::Diproses => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                        \App\Enums\OrderFulfillmentStatus::SiapDikirim => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300',
                        \App\Enums\OrderFulfillmentStatus::Selesai => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                        \App\Enums\OrderFulfillmentStatus::Dibatalkan => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
                    };
                @endphp

                <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col justify-between gap-3 border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-800/60 sm:flex-row sm:items-center">
                        <div>
                            <p class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $fulfillment->seller->pengelolaTps3rProfile?->nama_tps3r ?? $fulfillment->seller->name }}
                            </p>
                            <p class="text-xs text-zinc-500">Pengelola: {{ $fulfillment->seller->name }}</p>
                        </div>
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                            {{ $fulfillment->status->label() }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-zinc-500 dark:text-zinc-400">
                                <tr>
                                    <th class="px-5 py-3">Produk</th>
                                    <th class="px-5 py-3 text-right">Harga</th>
                                    <th class="px-5 py-3 text-right">Jumlah</th>
                                    <th class="px-5 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($sellerItems as $item)
                                    <tr>
                                        <td class="px-5 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product?->nama_produk ?? 'Produk tidak tersedia' }}</td>
                                        <td class="px-5 py-3 text-right text-zinc-600 dark:text-zinc-300">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="px-5 py-3 text-right text-zinc-600 dark:text-zinc-300">{{ $item->jumlah }}</td>
                                        <td class="px-5 py-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <th colspan="3" class="px-5 py-4 text-right text-zinc-600 dark:text-zinc-300">Subtotal TPS3R</th>
                                    <td class="px-5 py-4 text-right text-base font-black text-teal-700 dark:text-teal-400">Rp {{ number_format($sellerItems->sum('subtotal'), 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if ($fulfillment->catatan_status)
                        <p class="border-t border-zinc-100 px-5 py-3 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                            <span class="font-semibold">Catatan:</span> {{ $fulfillment->catatan_status }}
                        </p>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900">
                    Pesanan ini belum memiliki data fulfillment TPS3R.
                </div>
            @endforelse
        </section>

        <section class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <h2 class="font-bold text-zinc-900 dark:text-zinc-100">Alamat Pengiriman</h2>
                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $order->alamat_pengiriman ?: 'Alamat pengiriman tidak tersedia.' }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <h2 class="font-bold text-zinc-900 dark:text-zinc-100">Catatan Pembeli</h2>
                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $order->catatan ?: 'Tidak ada catatan tambahan.' }}</p>
            </div>
        </section>
    </div>
</x-layouts::app>
