<x-layouts::app :title="__('Pesanan Marketplace TPS3R')">
    <div class="civic-page mx-auto w-full max-w-7xl">
        <x-civic.subpage-header
            :breadcrumbLabel="$isAdmin ? 'Dashboard Admin' : 'Dashboard TPS3R'"
            :breadcrumbRoute="$isAdmin ? 'dashboard.admin' : 'dashboard.tps3r'"
            current="Pesanan Marketplace"
            eyebrow="Marketplace Ekonomi Sirkular"
            title="Pesanan Marketplace"
            :description="$isAdmin ? 'Pantau setiap fulfillment pesanan dari seluruh pengelola TPS3R Kota Madiun.' : 'Kelola dan proses pesanan produk daur ulang dari warga dan pelanggan TPS3R Anda.'"
            icon="shopping-bag"
        />

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Status Filter Tabs --}}
        <div data-ajax-zone="tps3r-orders" class="flex flex-col gap-6">
            <div class="civic-filter-rail w-full justify-start">
                @php
                    $tabList = [
                        ['key' => null, 'label' => 'Semua', 'count' => $statusCounts['semua'] ?? 0],
                        ['key' => 'menunggu', 'label' => 'Menunggu Diproses', 'count' => $statusCounts['menunggu'] ?? 0],
                        ['key' => 'diproses', 'label' => 'Sedang Diproses', 'count' => $statusCounts['diproses'] ?? 0],
                        ['key' => 'siap_dikirim', 'label' => 'Siap Dikirim', 'count' => $statusCounts['siap_dikirim'] ?? 0],
                        ['key' => 'selesai', 'label' => 'Selesai', 'count' => $statusCounts['selesai'] ?? 0],
                        ['key' => 'dibatalkan', 'label' => 'Dibatalkan', 'count' => $statusCounts['dibatalkan'] ?? 0],
                    ];
                @endphp

                @foreach ($tabList as $tab)
                    @php
                        $isActive = ($status === $tab['key']) || (empty($status) && $tab['key'] === null);
                    @endphp
                    <a
                        href="{{ $tab['key'] ? route('dashboard.tps3r.orders', ['status' => $tab['key']]) : route('dashboard.tps3r.orders') }}"
                        data-ajax-link
                        class="civic-filter-chip inline-flex items-center gap-2 !text-xs {{ $isActive ? 'civic-filter-chip-active' : '' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $isActive ? 'bg-civic-100 text-civic-800 dark:bg-civic-900 dark:text-civic-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <section class="civic-card overflow-hidden">
                <div class="civic-table-wrap rounded-none border-0">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>No. Pesanan & Waktu</th>
                                @if ($isAdmin)
                                    <th>Pengelola TPS3R</th>
                                @endif
                                <th>Pembeli</th>
                                <th>Produk TPS3R</th>
                                <th>Metode Bayar</th>
                                <th class="text-right">Subtotal TPS3R</th>
                                <th class="text-center">Status Pemrosesan</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fulfillments as $fulfillment)
                                @php
                                    $order = $fulfillment->order;
                                    $sellerItems = $order->items->filter(
                                        fn ($item) => $item->product?->seller_id === $fulfillment->seller_id,
                                    );
                                    $fStatus = $fulfillment->status->value;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-mono font-bold text-civic-700 dark:text-civic-300">{{ $order->nomor_pesanan }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $order->created_at->format('d M Y, H:i') }}</div>
                                    </td>
                                    @if ($isAdmin)
                                        <td>
                                            <div class="font-semibold text-slate-950 dark:text-white">
                                                {{ $fulfillment->seller->pengelolaTps3rProfile?->nama_tps3r ?? $fulfillment->seller->name }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $fulfillment->seller->name }}</div>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="font-semibold text-slate-950 dark:text-white">{{ $order->buyer->name }}</div>
                                        @if ($order->buyer->phone_number)
                                            <div class="mt-0.5 flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                                <flux:icon.phone class="size-3.5" />
                                                <span>{{ $order->buyer->phone_number }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-medium text-slate-800 dark:text-slate-200">
                                            {{ $sellerItems->map(fn ($item) => ($item->product?->nama_produk ?? 'Produk') . ' (x' . $item->jumlah . ')')->join(', ') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($order->metode_pembayaran === 'poin_sampah')
                                            <span class="civic-status-success">
                                                <flux:icon.wallet class="size-3.5" />
                                                Poin Sampah
                                            </span>
                                        @elseif ($order->metode_pembayaran === 'midtrans')
                                            <div class="space-y-1">
                                                <span class="civic-status-info">
                                                    <flux:icon.credit-card class="size-3.5" />
                                                    Midtrans
                                                </span>
                                                @if (in_array($order->status, \App\Models\Order::REVENUE_STATUSES, true))
                                                    <span class="block text-xs font-bold text-civic-700 dark:text-civic-300">
                                                        ✓ Sudah Lunas
                                                    </span>
                                                @else
                                                    <span class="block text-xs font-medium text-amber-700 dark:text-amber-300">
                                                        <span class="inline-flex items-center gap-1"><flux:icon.clock class="size-3.5" /> Menunggu Bayar</span>
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="civic-status-info">
                                                <flux:icon.banknotes class="size-3.5" />
                                                COD / Tunai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right font-bold text-slate-950 dark:text-white">
                                        Rp {{ number_format($sellerItems->sum('subtotal'), 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if ($fStatus === 'menunggu')
                                            <span class="civic-status-warning">Menunggu diproses</span>
                                        @elseif ($fStatus === 'diproses')
                                            <span class="civic-status-info">Sedang diproses</span>
                                        @elseif ($fStatus === 'siap_dikirim')
                                            <span class="civic-status bg-civic-100 text-civic-800 dark:bg-civic-950/60 dark:text-civic-300">Siap dikirim</span>
                                        @elseif ($fStatus === 'selesai')
                                            <span class="civic-status-success">Selesai</span>
                                        @elseif ($fStatus === 'dibatalkan')
                                            <span class="civic-status-danger">Dibatalkan</span>
                                        @else
                                            <span class="civic-status bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">{{ $fulfillment->status->label() }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('dashboard.tps3r.orders.show', $order) }}" class="civic-button-secondary !min-h-9 !px-3 !py-1.5 !text-xs">
                                            Lihat detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 8 : 7 }}" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                            <flux:icon.cube class="size-8 text-slate-400" />
                                            <p class="font-medium text-slate-700 dark:text-slate-200">Tidak ada pesanan ditemukan</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada pesanan dengan kriteria filter yang dipilih.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($fulfillments->hasPages())
                    <div class="border-t border-slate-100 p-4 dark:border-slate-800">{{ $fulfillments->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-layouts::app>
