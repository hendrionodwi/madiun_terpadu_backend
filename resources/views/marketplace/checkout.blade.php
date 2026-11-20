@extends('layouts.public', ['title' => 'Checkout Marketplace'])

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8" x-data="marketplaceCheckout()">
        @include('marketplace._feedback')
        <h1 class="font-display text-3xl font-extrabold text-slate-950">Checkout</h1>

        <form method="POST" action="{{ route('marketplace.checkout.store') }}" class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf
            <input type="hidden" name="seller_id" value="{{ $checkoutSellerId }}">
            <input type="hidden" name="shipping_source" :value="delivery === 'diantar' ? source : ''">
            <input type="hidden" name="destination_kelurahan" :value="destinationKelurahan">
            <input type="hidden" name="destination_kecamatan" :value="destinationKecamatan">
            <input type="hidden" name="rajaongkir_destination_id" :value="destinationId">
            <input type="hidden" name="shipping_courier" :value="selectedQuote?.courier_code ?? ''">
            <input type="hidden" name="shipping_service" :value="selectedQuote?.service ?? ''">

            <div class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-display text-lg font-extrabold text-slate-950">Pengiriman</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="cursor-pointer rounded-xl border border-slate-200 p-4 has-[:checked]:border-civic-600 has-[:checked]:bg-civic-50">
                            <input type="radio" name="metode_pengiriman" value="ambil_mandiri" x-model="delivery" @change="resetDelivery()" class="text-civic-700 focus:ring-civic-600">
                            <span class="ml-2 text-sm font-extrabold">Ambil mandiri</span>
                            <span class="mt-1 block pl-6 text-xs text-slate-500">Gratis di TPS3R</span>
                        </label>
                        <label class="cursor-pointer rounded-xl border border-slate-200 p-4 has-[:checked]:border-civic-600 has-[:checked]:bg-civic-50">
                            <input type="radio" name="metode_pengiriman" value="diantar" x-model="delivery" @change="activateDelivery()" class="text-civic-700 focus:ring-civic-600">
                            <span class="ml-2 text-sm font-extrabold">Diantar</span>
                            <span class="mt-1 block pl-6 text-xs text-slate-500">Armada TPS3R atau kurir</span>
                        </label>
                    </div>

                    <div x-show="delivery === 'diantar'" x-cloak class="mt-4 space-y-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <button type="button" @click="setSource('local_tps3r')" :class="source === 'local_tps3r' ? 'border-civic-600 bg-civic-50 text-civic-900' : 'border-slate-200 bg-white text-slate-700'" class="min-h-11 rounded-xl border px-4 text-left text-sm font-bold">
                                Armada TPS3R <span class="block text-xs font-normal">Kota Madiun</span>
                            </button>
                            <button type="button" @click="setSource('rajaongkir')" :disabled="!originId" :class="source === 'rajaongkir' ? 'border-civic-600 bg-civic-50 text-civic-900' : 'border-slate-200 bg-white text-slate-700'" class="min-h-11 rounded-xl border px-4 text-left text-sm font-bold disabled:cursor-not-allowed disabled:opacity-50">
                                Kurir luar kota <span class="block text-xs font-normal">Tarif RajaOngkir</span>
                            </button>
                        </div>

                        <div x-show="source === 'local_tps3r'" class="rounded-xl bg-slate-50 p-4">
                            @if ($deliveryZones->isNotEmpty())
                                <label class="block text-sm font-bold text-slate-700">Wilayah tujuan</label>
                                <select x-model="selectedZoneKey" @change="selectZone()" class="mt-2 min-h-11 w-full rounded-xl border-slate-200 bg-white focus:border-civic-600 focus:ring-civic-600">
                                    <option value="">Pilih kelurahan</option>
                                    @foreach ($deliveryZones as $zone)
                                        <option value="{{ $zone->kelurahan }}|{{ $zone->kecamatan }}">{{ $zone->kelurahan }}, {{ $zone->kecamatan }} — Rp{{ number_format($zone->delivery_fee, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-sm text-slate-600">Armada TPS3R belum memiliki wilayah pengiriman aktif.</p>
                            @endif
                        </div>

                        <div x-show="source === 'rajaongkir'" x-cloak class="space-y-3 rounded-xl bg-slate-50 p-4">
                            <label class="block text-sm font-bold text-slate-700">Cari kecamatan atau kota tujuan</label>
                            <div class="flex gap-2">
                                <input type="search" x-model="destinationSearch" @keydown.enter.prevent="searchDestinations()" placeholder="Contoh: Ponorogo" class="min-h-11 min-w-0 flex-1 rounded-xl border-slate-200 bg-white focus:border-civic-600 focus:ring-civic-600">
                                <button type="button" @click="searchDestinations()" :disabled="loadingDestinations" class="min-h-11 rounded-xl bg-slate-900 px-4 text-sm font-bold text-white disabled:opacity-50">Cari</button>
                            </div>
                            <div x-show="destinations.length" class="space-y-2">
                                <template x-for="destination in destinations" :key="destination.id">
                                    <button type="button" @click="selectDestination(destination)" :class="destinationId === destination.id ? 'border-civic-600 bg-civic-50' : 'border-slate-200 bg-white'" class="block min-h-11 w-full rounded-xl border px-3 py-2 text-left text-sm" x-text="destination.label"></button>
                                </template>
                            </div>
                            <div x-show="loadingQuotes" class="text-sm text-slate-500">Menghitung ongkir...</div>
                            <div x-show="quotes.length" class="grid gap-2 sm:grid-cols-2">
                                <template x-for="quote in quotes" :key="quote.courier_code + quote.service">
                                    <button type="button" @click="selectQuote(quote)" :class="selectedQuote === quote ? 'border-civic-600 bg-civic-50' : 'border-slate-200 bg-white'" class="min-h-11 rounded-xl border px-3 py-2 text-left text-sm">
                                        <strong x-text="quote.courier_name + ' ' + quote.service"></strong>
                                        <span class="block text-xs text-slate-500" x-text="rupiah(quote.cost) + (quote.etd ? ' · ' + quote.etd : '')"></span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="shippingError" x-text="shippingError" class="text-sm font-medium text-red-700"></p>
                        </div>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Alamat lengkap</span>
                            <textarea name="alamat_pengiriman" rows="3" class="mt-2 w-full rounded-xl border-slate-200 focus:border-civic-600 focus:ring-civic-600">{{ old('alamat_pengiriman', $wargaProfile?->alamat) }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-display text-lg font-extrabold text-slate-950">Pembayaran</h2>
                    <div class="mt-4 space-y-3">
                        <label :class="source === 'rajaongkir' && delivery === 'diantar' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'" class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 has-[:checked]:border-civic-600 has-[:checked]:bg-civic-50">
                            <input type="radio" name="metode_pembayaran" value="poin_sampah" x-model="payment" :disabled="source === 'rajaongkir' && delivery === 'diantar'" class="mt-1 text-civic-700 focus:ring-civic-600">
                            <span><strong class="block text-sm">Gunakan poin</strong><span class="text-xs text-slate-500">Saldo: {{ number_format($wargaProfile?->saldo_poin ?? 0, 0, ',', '.') }} poin · Khusus layanan TPS3R Madiun</span></span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 has-[:checked]:border-civic-600 has-[:checked]:bg-civic-50">
                            <input type="radio" name="metode_pembayaran" value="midtrans" x-model="payment" class="mt-1 text-civic-700 focus:ring-civic-600">
                            <span><strong class="block text-sm">Pembayaran online Midtrans</strong><span class="text-xs text-slate-500">QRIS, transfer bank, dan kanal tersedia.</span></span>
                        </label>
                    </div>
                    <label class="mt-4 block"><span class="text-sm font-bold text-slate-700">Catatan (opsional)</span><textarea name="catatan" rows="2" class="mt-2 w-full rounded-xl border-slate-200 focus:border-civic-600 focus:ring-civic-600">{{ old('catatan') }}</textarea></label>
                </section>
            </div>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 lg:sticky lg:top-24">
                <h2 class="font-display text-lg font-extrabold text-slate-950">Ringkasan pesanan</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($items as $item)
                        <div class="flex justify-between gap-3 text-sm"><span class="text-slate-600">{{ $item['product']->nama_produk }} × {{ $item['jumlah'] }}</span><strong>Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</strong></div>
                    @endforeach
                </div>
                <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span>Pengiriman</span><span x-text="fee > 0 ? rupiah(fee) : 'Gratis'"></span></div>
                    <div class="flex justify-between pt-2 text-base font-extrabold"><span>Total</span><span x-text="rupiah({{ (int) $subtotal }} + fee)"></span></div>
                </div>
                <button class="mt-5 min-h-11 w-full rounded-xl bg-civic-700 px-4 text-sm font-extrabold text-white hover:bg-civic-800">Buat pesanan</button>
            </aside>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        function marketplaceCheckout() {
            const config = {{ Illuminate\Support\Js::from($checkoutConfig) }};

            return {
                delivery: config.delivery,
                source: config.source,
                payment: config.payment,
                zones: config.zones,
                selectedZoneKey: config.selectedZoneKey,
                destinationKelurahan: config.destinationKelurahan,
                destinationKecamatan: config.destinationKecamatan,
                destinationId: Number(config.destinationId),
                destinationSearch: '',
                destinations: [],
                quotes: [],
                selectedQuote: null,
                fee: 0,
                originId: Number(config.originId),
                loadingDestinations: false,
                loadingQuotes: false,
                shippingError: '',
                init() {
                    this.selectZone();
                    if (this.delivery === 'ambil_mandiri') this.fee = 0;
                },
                rupiah(value) {
                    return 'Rp' + Number(value || 0).toLocaleString('id-ID');
                },
                activateDelivery() {
                    if (!this.source) this.source = 'local_tps3r';
                    this.source === 'local_tps3r' ? this.selectZone() : this.clearQuote();
                },
                resetDelivery() {
                    this.fee = 0;
                    this.payment = this.payment || 'midtrans';
                },
                setSource(source) {
                    this.source = source;
                    this.shippingError = '';
                    if (source === 'rajaongkir') {
                        this.payment = 'midtrans';
                        this.destinationKelurahan = '';
                        this.destinationKecamatan = '';
                        this.clearQuote();
                    } else {
                        this.destinationId = 0;
                        this.quotes = [];
                        this.selectedQuote = null;
                        this.selectZone();
                    }
                },
                selectZone() {
                    const zone = this.zones.find(item => item.key === this.selectedZoneKey);
                    this.destinationKelurahan = zone?.kelurahan ?? '';
                    this.destinationKecamatan = zone?.kecamatan ?? '';
                    this.fee = this.delivery === 'diantar' && this.source === 'local_tps3r' ? Number(zone?.fee ?? 0) : 0;
                },
                clearQuote() {
                    this.selectedQuote = null;
                    this.fee = 0;
                },
                async searchDestinations() {
                    if (this.destinationSearch.trim().length < 3) {
                        this.shippingError = 'Masukkan minimal 3 karakter.';
                        return;
                    }
                    this.loadingDestinations = true;
                    this.shippingError = '';
                    try {
                        const url = new URL(config.destinationsUrl, window.location.origin);
                        url.searchParams.set('search', this.destinationSearch.trim());
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Tujuan tidak dapat dimuat.');
                        this.destinations = payload.data ?? [];
                    } catch (error) {
                        this.shippingError = error.message;
                    } finally {
                        this.loadingDestinations = false;
                    }
                },
                async selectDestination(destination) {
                    this.destinationId = Number(destination.id);
                    this.destinationKelurahan = destination.subdistrict_name ?? '';
                    this.destinationKecamatan = destination.district_name ?? '';
                    this.loadingQuotes = true;
                    this.shippingError = '';
                    this.clearQuote();
                    try {
                        const response = await fetch(config.quotesUrl, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                            body: JSON.stringify({ origin_id: this.originId, destination_id: this.destinationId, weight_grams: config.cartWeight }),
                        });
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Ongkir tidak dapat dihitung.');
                        this.quotes = payload.data ?? [];
                    } catch (error) {
                        this.shippingError = error.message;
                    } finally {
                        this.loadingQuotes = false;
                    }
                },
                selectQuote(quote) {
                    this.selectedQuote = quote;
                    this.fee = Number(quote.cost || 0);
                },
            };
        }
    </script>
@endpush
