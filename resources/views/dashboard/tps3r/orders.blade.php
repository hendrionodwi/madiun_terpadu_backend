<x-layouts::app :title="__('Pesanan Marketplace TPS3R')">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-2 md:p-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Pesanan Marketplace</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $isAdmin ? 'Pantau setiap fulfillment pesanan dari seluruh pengelola TPS3R Kota Madiun.' : 'Kelola dan proses pesanan produk daur ulang dari warga dan pelanggan TPS3R Anda.' }}
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        {{-- Status Filter Tabs --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-3 dark:border-zinc-800">
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
                    class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-semibold transition-all {{ $isActive ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-zinc-600 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700/60 border border-zinc-200 dark:border-zinc-700' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $isActive ? 'bg-teal-900/60 text-white' : 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-3 py-3">No. Pesanan & Waktu</th>
                            @if ($isAdmin)
                                <th class="px-3 py-3">Pengelola TPS3R</th>
                            @endif
                            <th class="px-3 py-3">Pembeli</th>
                            <th class="px-3 py-3">Produk TPS3R</th>
                            <th class="px-3 py-3">Metode Bayar</th>
                            <th class="px-3 py-3 text-right">Subtotal TPS3R</th>
                            <th class="px-3 py-3 text-center">Status Pemrosesan</th>
                            <th class="px-3 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($fulfillments as $fulfillment)
                            @php
                                $order = $fulfillment->order;
                                $sellerItems = $order->items->filter(
                                    fn ($item) => $item->product?->seller_id === $fulfillment->seller_id,
                                );
                                $fStatus = $fulfillment->status->value;
                            @endphp
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 text-zinc-700 dark:text-zinc-200 transition">
                                <td class="px-3 py-3.5">
                                    <div class="font-mono font-bold text-teal-700 dark:text-teal-400">{{ $order->nomor_pesanan }}</div>
                                    <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $order->created_at->format('d M Y, H:i') }}</div>
                                </td>
                                @if ($isAdmin)
                                    <td class="px-3 py-3.5">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $fulfillment->seller->pengelolaTps3rProfile?->nama_tps3r ?? $fulfillment->seller->name }}
                                        </div>
                                        <div class="text-xs text-zinc-500">{{ $fulfillment->seller->name }}</div>
                                    </td>
                                @endif
                                <td class="px-3 py-3.5">
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->buyer->name }}</div>
                                    @if ($order->buyer->phone_number)
                                        <div class="mt-0.5 flex items-center gap-1 text-xs text-zinc-500">
                                            <flux:icon.phone class="size-3.5" />
                                            <span>{{ $order->buyer->phone_number }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $sellerItems->map(fn ($item) => ($item->product?->nama_produk ?? 'Produk') . ' (x' . $item->jumlah . ')')->join(', ') }}
                                    </div>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if ($order->metode_pembayaran === 'poin_sampah')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            <flux:icon.wallet class="size-3.5" />
                                            Poin Sampah
                                        </span>
                                    @elseif ($order->metode_pembayaran === 'midtrans')
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                                <flux:icon.credit-card class="size-3.5" />
                                                Midtrans
                                            </span>
                                            @if (in_array($order->status, \App\Models\Order::REVENUE_STATUSES, true))
                                                <span class="block text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                                    ✓ Sudah Lunas
                                                </span>
                                            @else
                                                <span class="block text-[11px] font-medium text-amber-600 dark:text-amber-400">
                                                    <span class="inline-flex items-center gap-1"><flux:icon.clock class="size-3.5" /> Menunggu Bayar</span>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                            <flux:icon.banknotes class="size-3.5" />
                                            COD / Tunai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-right font-bold text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($sellerItems->sum('subtotal'), 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    @if ($fStatus === 'menunggu')
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Menunggu diproses
                                        </span>
                                    @elseif ($fStatus === 'diproses')
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                            Sedang diproses
                                        </span>
                                    @elseif ($fStatus === 'siap_dikirim')
                                        <span class="inline-flex rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                            Siap dikirim
                                        </span>
                                    @elseif ($fStatus === 'selesai')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Selesai
                                        </span>
                                    @elseif ($fStatus === 'dibatalkan')
                                        <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            Dibatalkan
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $fulfillment->status->label() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <a href="{{ route('dashboard.tps3r.orders.show', $order) }}" class="inline-flex items-center gap-1 rounded-lg border border-teal-700 px-3 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-50 dark:border-teal-400 dark:text-teal-300 dark:hover:bg-teal-950/50">
                                        Lihat detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 7 }}" class="px-3 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <flux:icon.cube class="size-8 text-zinc-400" />
                                        <p class="font-medium text-zinc-700 dark:text-zinc-200">Tidak ada pesanan ditemukan</p>
                                        <p class="text-xs text-zinc-500">Belum ada pesanan dengan kriteria filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $fulfillments->links() }}</div>
        </section>
    </div>
</x-layouts::app>
