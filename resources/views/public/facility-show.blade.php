@extends('layouts.public', ['title' => $facility->nama_tps3r])

@section('content')
    @php
        $services = collect($facility->layanan_tersedia ?? [])->filter()->values();
        $acceptedWaste = collect($facility->kategori_sampah_diterima ?? [])->filter()->values();
        $publicPhone = preg_replace('/[^0-9+]/', '', (string) $facility->kontak_publik);
        $location = collect([$facility->kelurahan, $facility->kecamatan])->filter()->implode(', ');
    @endphp

    <section class="border-b border-slate-200 bg-gradient-to-b from-civic-50/80 via-white to-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            <a href="{{ route('public.services') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-bold text-civic-700 hover:text-civic-900">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
                {{ __('portal.facility.back') }}
            </a>

            <div class="mt-4 overflow-hidden rounded-3xl border border-civic-200/80 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
                <div class="relative min-h-64 bg-civic-950 sm:min-h-80">
                    @if ($facility->foto_url)
                        <img src="{{ $facility->foto_url }}" alt="Fasilitas {{ $facility->nama_tps3r }}" class="absolute inset-0 h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-civic-950 via-civic-950/35 to-transparent"></div>
                    @else
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_10%,rgba(217,119,6,0.45),transparent_34%),linear-gradient(135deg,#043c34,#046a58)]"></div>
                    @endif

                    <div class="relative flex min-h-64 items-end p-5 text-white sm:min-h-80 sm:p-8 lg:p-10">
                        <div class="max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-extrabold ring-1 ring-white/25 backdrop-blur">{{ __('portal.facility.registered') }}</span>
                                @if ($facility->kode_tps3r)
                                    <span class="rounded-full bg-civic-300/15 px-3 py-1 font-mono text-xs font-bold text-civic-50 ring-1 ring-civic-200/30">{{ $facility->kode_tps3r }}</span>
                                @endif
                            </div>
                            <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">{{ $facility->nama_tps3r }}</h1>
                            <p class="mt-3 flex items-center gap-2 text-sm font-semibold text-civic-50/90 sm:text-base">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                {{ $location ?: __('portal.common.city') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid divide-y divide-slate-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="p-5 sm:p-6">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('portal.facility.operating_hours') }}</p>
                        <p class="mt-2 font-display text-base font-extrabold text-slate-950">{{ $facility->jam_operasional ?: __('portal.facility.contact_manager') }}</p>
                    </div>
                    <div class="p-5 sm:p-6">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('portal.facility.daily_capacity') }}</p>
                        <p class="mt-2 font-display text-base font-extrabold text-slate-950">
                            {{ $facility->kapasitas_harian_kg ? number_format($facility->kapasitas_harian_kg, 0, ',', '.').' kg' : __('portal.facility.not_listed') }}
                        </p>
                    </div>
                    <div class="p-5 sm:p-6">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('portal.facility.available_products') }}</p>
                        <p class="mt-2 font-display text-base font-extrabold text-slate-950">{{ $products->count() }} {{ __('portal.common.products') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-8">
        <div class="space-y-6">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-civic-700">{{ __('portal.facility.about_kicker') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-950">{{ __('portal.facility.about_title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600 sm:text-base">
                    {{ $facility->deskripsi ?: __('portal.facility.about_fallback') }}
                </p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-civic-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-civic-700">{{ __('portal.facility.processing_focus') }}</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $facility->fokus_pengolahan ?: __('portal.facility.processing_fallback') }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-800">{{ __('portal.facility.service_area') }}</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-800">{{ $facility->cakupan_wilayah ?: ($location ?: __('portal.common.city')) }}</p>
                    </div>
                </div>
            </article>

            <div class="grid gap-6 md:grid-cols-2">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-civic-100 text-civic-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19.5V5a2 2 0 0 1 2-2h12v18H6a2 2 0 0 1-2-1.5ZM8 7h6M8 11h6M8 15h4" /></svg>
                        </span>
                        <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.facility.services') }}</h2>
                    </div>
                    <ul class="mt-5 space-y-3">
                        @forelse ($services as $service)
                            <li class="flex items-start gap-3 text-sm leading-6 text-slate-700">
                                <svg class="mt-1 size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                                <span>{{ $service }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">{{ __('portal.facility.services_empty') }}</li>
                        @endforelse
                    </ul>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 22V12M12 12C8 12 5 9 5 5c4 0 7 3 7 7ZM12 15c4 0 7-3 7-7-4 0-7 3-7 7Z" /></svg>
                        </span>
                        <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.facility.accepted_waste') }}</h2>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @forelse ($acceptedWaste as $waste)
                            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ $waste }}</span>
                        @empty
                            <span class="text-sm text-slate-500">{{ __('portal.facility.accepted_empty') }}</span>
                        @endforelse
                    </div>
                </article>
            </div>

            @if ($products->isNotEmpty())
                <section>
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-civic-700">{{ __('portal.facility.marketplace') }}</p>
                            <h2 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-slate-950">{{ __('portal.facility.products_title') }}</h2>
                        </div>
                        <a href="{{ route('public.marketplace', ['store' => $facility->public_id]) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-civic-700 hover:text-civic-900">{{ __('portal.facility.all_products') }}</a>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        @foreach ($products as $product)
                            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                <a href="{{ route('marketplace.products.show', $product) }}" class="block">
                                    <div class="aspect-square bg-slate-100">
                                        @if ($product->foto_url)
                                            <img src="{{ $product->foto_url }}" alt="{{ $product->nama_produk }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-slate-400">
                                                <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 19V5h16v14H4Zm3-4 3-3 2 2 2-2 3 3" /><circle cx="9" cy="9" r="1" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-3 sm:p-4">
                                        <p class="line-clamp-2 text-sm font-bold leading-5 text-slate-900">{{ $product->nama_produk }}</p>
                                        <p class="mt-2 font-display text-sm font-extrabold text-civic-800">Rp{{ number_format($product->harga, 0, ',', '.') }}</p>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.facility.information') }}</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-bold text-slate-500">{{ __('portal.facility.address') }}</dt>
                        <dd class="mt-1 leading-6 text-slate-800">{{ $facility->alamat ?: __('portal.facility.address_empty') }}</dd>
                    </div>
                    @if ($facility->nama_koordinator)
                        <div>
                            <dt class="font-bold text-slate-500">{{ __('portal.facility.coordinator') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ $facility->nama_koordinator }}</dd>
                        </div>
                    @endif
                    @if ($facility->kontak_publik)
                        <div>
                            <dt class="font-bold text-slate-500">{{ __('portal.facility.contact') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ $facility->kontak_publik }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-5 grid gap-2">
                    @if ($publicPhone)
                        <a href="tel:{{ $publicPhone }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-civic-700 px-4 text-sm font-bold text-white hover:bg-civic-800">{{ __('portal.facility.call') }}</a>
                    @endif
                    @if ($facility->latitude && $facility->longitude)
                        <a href="{{ route('public.services', ['search' => $facility->nama_tps3r]) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-civic-200 bg-white px-4 text-sm font-bold text-civic-800 hover:bg-civic-50">{{ __('portal.facility.view_map') }}</a>
                    @endif
                </div>
            </div>

            @if ($deliveryZones->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.facility.delivery_areas') }}</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('portal.facility.delivery_description') }}</p>
                    <ul class="mt-4 space-y-3">
                        @foreach ($deliveryZones as $zone)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="font-semibold text-slate-800">{{ $zone->kelurahan }}, {{ $zone->kecamatan }}</span>
                                <span class="shrink-0 text-xs font-bold text-civic-700">{{ $zone->delivery_fee > 0 ? 'Rp'.number_format($zone->delivery_fee, 0, ',', '.') : __('portal.common.free') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </section>

    <x-public.action-band />
@endsection
