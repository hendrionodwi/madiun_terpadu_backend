<x-layouts::app :title="__('Detail Monitoring Pesanan')">
    <div class="civic-page mx-auto w-full max-w-7xl">
        <x-civic.subpage-header
            breadcrumbLabel="Monitoring Pesanan"
            breadcrumbRoute="dashboard.tps3r.orders"
            :current="$order->nomor_pesanan"
            eyebrow="Monitoring Marketplace se-Kota"
            :title="$order->nomor_pesanan"
            :description="'Dibuat pada ' . $order->created_at->format('d M Y, H:i:s')"
            icon="shopping-bag"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.tps3r.orders') }}" class="civic-button-secondary">
                    &larr; Kembali ke monitoring pesanan
                </a>
            </x-slot:actions>
        </x-civic.subpage-header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pembeli</p>
                <p class="mt-2 font-bold text-slate-950 dark:text-white">{{ $order->buyer->name }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $order->buyer->phone_number ?: $order->buyer->email }}</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status Pesanan</p>
                <p class="mt-2 font-bold capitalize text-slate-950 dark:text-white">{{ str_replace('_', ' ', $order->status) }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Status agregat seluruh TPS3R</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Metode Pembayaran</p>
                <p class="mt-2 font-bold capitalize text-slate-950 dark:text-white">{{ str_replace('_', ' ', $order->metode_pembayaran) }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $order->paid_at ? 'Lunas ' . $order->paid_at->format('d M Y, H:i') : 'Belum tercatat lunas' }}</p>
            </div>
            <div class="civic-stat-card">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Pesanan</p>
                <p class="mt-2 font-display text-xl font-extrabold text-civic-700 dark:text-civic-300">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $order->fulfillments->count() }} fulfillment TPS3R</p>
            </div>
        </section>

        <section class="flex flex-col gap-4">
            <div>
                <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Rincian Fulfillment per TPS3R</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Setiap status dan subtotal ditampilkan terpisah agar transaksi multi-penjual dapat dipantau dengan tepat.</p>
            </div>

            @forelse ($order->fulfillments as $fulfillment)
                @php
                    $sellerItems = $order->items->filter(
                        fn ($item) => $item->product?->seller_id === $fulfillment->seller_id,
                    );
                    $badgeClass = match ($fulfillment->status) {
                        \App\Enums\OrderFulfillmentStatus::Menunggu => 'civic-status-warning',
                        \App\Enums\OrderFulfillmentStatus::Diproses => 'civic-status-info',
                        \App\Enums\OrderFulfillmentStatus::SiapDikirim => 'civic-status bg-civic-100 text-civic-800 dark:bg-civic-950/60 dark:text-civic-300',
                        \App\Enums\OrderFulfillmentStatus::Selesai => 'civic-status-success',
                        \App\Enums\OrderFulfillmentStatus::Dibatalkan => 'civic-status-danger',
                    };
                @endphp

                <article class="civic-card overflow-hidden">
                    <div class="civic-section-header">
                        <div>
                            <p class="font-bold text-slate-950 dark:text-white">
                                {{ $fulfillment->seller->pengelolaTps3rProfile?->nama_tps3r ?? $fulfillment->seller->name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pengelola: {{ $fulfillment->seller->name }}</p>
                        </div>
                        <span class="w-fit {{ $badgeClass }}">
                            {{ $fulfillment->status->label() }}
                        </span>
                    </div>

                    <div class="civic-table-wrap rounded-none border-0">
                        <table class="civic-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Jumlah</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sellerItems as $item)
                                    <tr>
                                        <td class="font-medium text-slate-900 dark:text-slate-100">{{ $item->product?->nama_produk ?? 'Produk tidak tersedia' }}</td>
                                        <td class="text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="text-right text-slate-600 dark:text-slate-300">{{ $item->jumlah }}</td>
                                        <td class="text-right font-semibold text-slate-950 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th colspan="3" class="px-4 py-4 text-right text-slate-600 dark:text-slate-300">Subtotal TPS3R</th>
                                    <td class="px-4 py-4 text-right font-display text-base font-extrabold text-civic-700 dark:text-civic-300">Rp {{ number_format($sellerItems->sum('subtotal'), 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if ($fulfillment->catatan_status)
                        <p class="border-t border-slate-100 px-5 py-3 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-300">
                            <span class="font-semibold">Catatan:</span> {{ $fulfillment->catatan_status }}
                        </p>
                    @endif
                </article>
            @empty
                <div class="civic-empty py-12">
                    Pesanan ini belum memiliki data fulfillment TPS3R.
                </div>
            @endforelse
        </section>

        <section class="civic-card grid gap-4 p-5 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <h2 class="font-display font-bold text-slate-950 dark:text-white">Alamat Pengiriman</h2>
                <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $order->alamat_pengiriman ?: 'Alamat pengiriman tidak tersedia.' }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <h2 class="font-display font-bold text-slate-950 dark:text-white">Catatan Pembeli</h2>
                <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $order->catatan ?: 'Tidak ada catatan tambahan.' }}</p>
            </div>
        </section>
    </div>
</x-layouts::app>
