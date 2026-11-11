<x-layouts::app :title="__('Detail Pesanan Marketplace TPS3R')">
    @php
        $badgeClass = match ($fulfillment->status) {
            \App\Enums\OrderFulfillmentStatus::Menunggu => 'civic-status-warning',
            \App\Enums\OrderFulfillmentStatus::Diproses => 'civic-status-info',
            \App\Enums\OrderFulfillmentStatus::SiapDikirim => 'civic-status bg-civic-100 text-civic-800 dark:bg-civic-950/60 dark:text-civic-300',
            \App\Enums\OrderFulfillmentStatus::Selesai => 'civic-status-success',
            \App\Enums\OrderFulfillmentStatus::Dibatalkan => 'civic-status-danger',
        };
    @endphp

    <div class="civic-page mx-auto w-full max-w-7xl">
        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <x-civic.subpage-header
            breadcrumbLabel="Pesanan Marketplace"
            breadcrumbRoute="dashboard.tps3r.orders"
            :current="$order->nomor_pesanan"
            eyebrow="Detail Pesanan Marketplace"
            :title="$order->nomor_pesanan"
            :description="'Dibuat pada ' . $order->created_at->format('d M Y, H:i:s')"
            icon="shopping-bag"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.tps3r.orders') }}" class="civic-button-secondary">
                    &larr; Kembali ke daftar pesanan
                </a>
            </x-slot:actions>
        </x-civic.subpage-header>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="civic-card p-5 lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                    <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Daftar Produk TPS3R Anda</h2>
                    <span class="{{ $badgeClass }}">
                        {{ $fulfillment->status->label() }}
                    </span>
                </div>

                <div class="civic-table-wrap mt-5">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="font-semibold text-slate-950 dark:text-white">{{ $item->product->nama_produk }}</td>
                                    <td class="text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="text-right text-slate-600 dark:text-slate-300">{{ $item->jumlah }} item</td>
                                    <td class="text-right font-semibold text-slate-950 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 dark:border-slate-700">
                            <tr>
                                <th colspan="3" class="px-4 py-4 text-right text-slate-700 dark:text-slate-200">Total Tagihan TPS3R Anda:</th>
                                <td class="px-4 py-4 text-right font-display text-base font-extrabold text-civic-700 dark:text-civic-300">Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($fulfillment->catatan_status)
                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-800/60">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Catatan Status Saat Ini:</p>
                        <p class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200">{{ $fulfillment->catatan_status }}</p>
                    </div>
                @endif

                <div class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-800">
                    <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Perbarui Status Pemrosesan</h3>

                    @if ($fulfillment->status->allowedTransitions() !== [])
                        <form method="POST" action="{{ route('dashboard.tps3r.fulfillments.update', $fulfillment) }}" class="mt-4 grid gap-4">
                            @csrf
                            @method('PATCH')

                            <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                                <span>Catatan untuk pembeli (opsional)</span>
                                <textarea name="catatan_status" rows="3" maxlength="500" placeholder="Misal: Pesanan sedang dipacking / Resi pengiriman / Alasan pembatalan" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900">{{ old('catatan_status', $fulfillment->catatan_status) }}</textarea>
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
                                        class="{{ $nextStatus === \App\Enums\OrderFulfillmentStatus::Dibatalkan ? 'inline-flex min-h-11 items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700' : 'civic-button-primary' }}"
                                    >
                                        Ubah menjadi {{ $nextStatus->label() }}
                                    </button>
                                @endforeach
                            </div>
                        </form>
                    @else
                        <div class="civic-empty mt-3 py-4 text-left">
                            Pesanan ini telah berada di status akhir (<strong>{{ strtolower($fulfillment->status->label()) }}</strong>) dan tidak dapat diubah lagi.
                        </div>
                    @endif
                </div>
            </section>

            <aside class="flex flex-col gap-5">
                {{-- Customer Info --}}
                <div class="civic-card p-5">
                    <h2 class="border-b border-slate-100 pb-3 font-display text-base font-extrabold text-slate-950 dark:border-slate-800 dark:text-white">
                        Kontak & Info Pembeli
                    </h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Nama Pembeli</dt>
                            <dd class="mt-0.5 font-semibold text-slate-950 dark:text-white">{{ $order->buyer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Email</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-300">{{ $order->buyer->email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Nomor Telepon</dt>
                            <dd class="mt-0.5 font-semibold text-slate-950 dark:text-white">{{ $order->buyer->phone_number ?? '-' }}</dd>
                            @if ($order->buyer->phone_number)
                                <div class="mt-2.5 flex flex-wrap gap-2">
                                    <a
                                        href="tel:{{ $order->buyer->phone_number }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
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
                                        class="civic-button-secondary !min-h-8 !px-2.5 !py-1.5 !text-xs"
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
                <div class="civic-card p-5">
                    <h2 class="border-b border-slate-100 pb-3 font-display text-base font-extrabold text-slate-950 dark:border-slate-800 dark:text-white">
                        Pengiriman & Pembayaran
                    </h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Metode Pembayaran</dt>
                            <dd class="mt-1">
                                @if ($order->metode_pembayaran === 'poin_sampah')
                                    <span class="civic-status-success">
                                        <flux:icon.wallet class="size-3.5" />
                                        Saldo Poin Sampah
                                    </span>
                                @elseif ($order->metode_pembayaran === 'midtrans')
                                    <div class="space-y-1">
                                        <span class="civic-status-info">
                                            <flux:icon.credit-card class="size-3.5" />
                                            Midtrans (Transfer Bank / QRIS)
                                        </span>
                                        <div class="text-xs">
                                            @if ($order->status === 'dibayar')
                                                <span class="font-bold text-civic-700 dark:text-civic-300">
                                                    ✓ Lunas ({{ $order->paid_at ? $order->paid_at->format('d M Y, H:i') : 'Terbayar' }})
                                                </span>
                                            @else
                                                <span class="text-amber-700 dark:text-amber-300">
                                                    <span class="inline-flex items-center gap-1"><flux:icon.clock class="size-3.5" /> Menunggu Pembayaran</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="civic-status-info">
                                        <flux:icon.banknotes class="size-3.5" />
                                        COD / Bayar Tunai
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Alamat Pengiriman</dt>
                            <dd class="mt-1 whitespace-pre-line rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-medium text-slate-800 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">
                                {{ $order->alamat_pengiriman ?? 'Ambil di lokasi TPS3R' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Fulfillment Timeline --}}
                <div class="civic-card p-5">
                    <h2 class="border-b border-slate-100 pb-3 font-display text-base font-extrabold text-slate-950 dark:border-slate-800 dark:text-white">
                        Riwayat Waktu Proses
                    </h2>
                    <ul class="mt-4 space-y-3 text-xs">
                        <li class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Pesanan Masuk</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Mulai Diproses</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $fulfillment->processed_at ? $fulfillment->processed_at->format('d M Y, H:i') : '-' }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Siap Dikirim</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $fulfillment->ready_at ? $fulfillment->ready_at->format('d M Y, H:i') : '-' }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Selesai Diterima</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $fulfillment->completed_at ? $fulfillment->completed_at->format('d M Y, H:i') : '-' }}</span>
                        </li>
                        @if ($fulfillment->cancelled_at)
                            <li class="flex items-center justify-between text-red-600 dark:text-red-400">
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
