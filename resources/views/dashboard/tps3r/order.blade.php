<x-layouts::app :title="__('Detail Pesanan Marketplace TPS3R')">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 p-2 md:p-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-teal-700 dark:text-teal-400">Detail Pesanan Marketplace</p>
                <h1 class="font-mono text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $order->nomor_pesanan }}</h1>
                <p class="text-xs text-zinc-500">Dibuat pada {{ $order->created_at->format('d M Y, H:i:s') }}</p>
            </div>
            <a href="{{ route('dashboard.tps3r.orders') }}" class="inline-flex w-fit items-center gap-1.5 rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                &larr; Kembali ke daftar pesanan
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 pb-4 dark:border-zinc-800">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Daftar Produk TPS3R Anda</h2>
                    @php
                        $badgeClass = match ($fulfillment->status) {
                            \App\Enums\OrderFulfillmentStatus::Menunggu => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                            \App\Enums\OrderFulfillmentStatus::Diproses => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                            \App\Enums\OrderFulfillmentStatus::SiapDikirim => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300',
                            \App\Enums\OrderFulfillmentStatus::Selesai => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                            \App\Enums\OrderFulfillmentStatus::Dibatalkan => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
                        };
                    @endphp
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                        {{ $fulfillment->status->label() }}
                    </span>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-3 py-3">Produk</th>
                                <th class="px-3 py-3 text-right">Harga Satuan</th>
                                <th class="px-3 py-3 text-right">Jumlah</th>
                                <th class="px-3 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($order->items as $item)
                                <tr class="text-zinc-700 dark:text-zinc-200">
                                    <td class="px-3 py-3 font-semibold">{{ $item->product->nama_produk }}</td>
                                    <td class="px-3 py-3 text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-right">{{ $item->jumlah }} item</td>
                                    <td class="px-3 py-3 text-right font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th colspan="3" class="px-3 py-4 text-right text-zinc-700 dark:text-zinc-200">Total Tagihan TPS3R Anda:</th>
                                <td class="px-3 py-4 text-right text-base font-bold text-teal-700 dark:text-teal-400">Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($fulfillment->catatan_status)
                    <div class="mt-4 rounded-xl border border-zinc-200 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/60">
                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Catatan Status Saat Ini:</p>
                        <p class="mt-1 text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $fulfillment->catatan_status }}</p>
                    </div>
                @endif

                <div class="mt-6 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    <h3 class="font-bold text-zinc-900 dark:text-zinc-100">Perbarui Status Pemrosesan</h3>

                    @if ($fulfillment->status->allowedTransitions() !== [])
                        <form method="POST" action="{{ route('dashboard.tps3r.fulfillments.update', $fulfillment) }}" class="mt-4 grid gap-4">
                            @csrf
                            @method('PATCH')

                            <label class="grid gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                Catatan untuk pembeli (opsional)
                                <textarea name="catatan_status" rows="3" maxlength="500" placeholder="Misal: Pesanan sedang dipacking / Resi pengiriman / Alasan pembatalan" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">{{ old('catatan_status', $fulfillment->catatan_status) }}</textarea>
                                @error('catatan_status')
                                    <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
                                @enderror
                            </label>

                            @error('status')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            <div class="flex flex-wrap items-center gap-3">
                                @foreach ($fulfillment->status->allowedTransitions() as $nextStatus)
                                    <button
                                        type="submit"
                                        name="status"
                                        value="{{ $nextStatus->value }}"
                                        onclick="return {{ $nextStatus === \App\Enums\OrderFulfillmentStatus::Dibatalkan ? 'confirm(\'Apakah Anda yakin ingin membatalkan pesanan ini? Stok produk dan saldo poin pembeli akan otomatis dikembalikan.\')' : 'true' }};"
                                        class="rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition shadow-sm {{ $nextStatus === \App\Enums\OrderFulfillmentStatus::Dibatalkan ? 'bg-rose-600 hover:bg-rose-700' : 'bg-teal-700 hover:bg-teal-800' }}"
                                    >
                                        Ubah menjadi {{ $nextStatus->label() }}
                                    </button>
                                @endforeach
                            </div>
                        </form>
                    @else
                        <div class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-400">
                            Pesanan ini telah berada di status akhir (<strong>{{ strtolower($fulfillment->status->label()) }}</strong>) dan tidak dapat diubah lagi.
                        </div>
                    @endif
                </div>
            </section>

            <aside class="flex flex-col gap-5">
                {{-- Customer Info --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                        Kontak & Info Pembeli
                    </h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Nama Pembeli</dt>
                            <dd class="mt-0.5 font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->buyer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Email</dt>
                            <dd class="mt-0.5 font-medium text-zinc-700 dark:text-zinc-300">{{ $order->buyer->email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Nomor Telepon</dt>
                            <dd class="mt-0.5 font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->buyer->phone_number ?? '-' }}</dd>
                            @if ($order->buyer->phone_number)
                                <div class="mt-2.5 flex flex-wrap gap-2">
                                    <a
                                        href="tel:{{ $order->buyer->phone_number }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                    >
                                        <flux:icon.phone class="size-4" />
                                        Telepon
                                    </a>
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $order->buyer->phone_number);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                        $waMessage = 'Halo Bapak/Ibu ' . $order->buyer->name . ', kami dari TPS3R ingin konfirmasi terkait pesanan Marketplace Anda no. ' . $order->nomor_pesanan;
                                    @endphp
                                    <a
                                        href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waMessage) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
                                    >
                                        <flux:icon.chat-bubble-left-right class="size-4" />
                                        WhatsApp
                                    </a>
                                </div>
                            @endif
                        </div>
                    </dl>
                </div>

                {{-- Delivery & Payment Info --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                        Pengiriman & Pembayaran
                    </h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Metode Pembayaran</dt>
                            <dd class="mt-1">
                                @if ($order->metode_pembayaran === 'poin_sampah')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <flux:icon.wallet class="size-3.5" />
                                        Saldo Poin Sampah
                                    </span>
                                @elseif ($order->metode_pembayaran === 'midtrans')
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                            <flux:icon.credit-card class="size-3.5" />
                                            Midtrans (Transfer Bank / QRIS)
                                        </span>
                                        <div class="text-xs">
                                            @if ($order->status === 'dibayar')
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                    ✓ Lunas ({{ $order->paid_at ? $order->paid_at->format('d M Y, H:i') : 'Terbayar' }})
                                                </span>
                                            @else
                                                <span class="text-amber-600 dark:text-amber-400">
                                                    <span class="inline-flex items-center gap-1"><flux:icon.clock class="size-3.5" /> Menunggu Pembayaran</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                        <flux:icon.banknotes class="size-3.5" />
                                        COD / Bayar Tunai
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Alamat Pengiriman</dt>
                            <dd class="mt-1 whitespace-pre-line font-medium text-zinc-800 dark:text-zinc-200 bg-zinc-50 dark:bg-zinc-800/60 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                {{ $order->alamat_pengiriman ?? 'Ambil di lokasi TPS3R' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Fulfillment Timeline --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                        Riwayat Waktu Proses
                    </h2>
                    <ul class="mt-4 space-y-3 text-xs">
                        <li class="flex items-center justify-between">
                            <span class="text-zinc-500">Pesanan Masuk</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-zinc-500">Mulai Diproses</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $fulfillment->processed_at ? $fulfillment->processed_at->format('d M Y, H:i') : '-' }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-zinc-500">Siap Dikirim</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $fulfillment->ready_at ? $fulfillment->ready_at->format('d M Y, H:i') : '-' }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-zinc-500">Selesai Diterima</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $fulfillment->completed_at ? $fulfillment->completed_at->format('d M Y, H:i') : '-' }}</span>
                        </li>
                        @if ($fulfillment->cancelled_at)
                            <li class="flex items-center justify-between text-rose-600 dark:text-rose-400">
                                <span>Dibatalkan Pada</span>
                                <span class="font-medium">{{ $fulfillment->cancelled_at->format('d M Y, H:i') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</x-layouts::app>
