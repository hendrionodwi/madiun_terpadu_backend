@extends('layouts.public', ['title' => __('portal.services.title')])

@section('content')
    <section class="border-b border-slate-200 bg-gradient-to-b from-civic-50/75 via-white to-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 portal-settle">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-700">{{ __('portal.services.kicker') }}</p>
            <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="font-display text-4xl font-extrabold tracking-tight text-slate-950">{{ __('portal.services.heading') }}</h1>
                    <p class="mt-3 max-w-2xl text-base leading-7 text-slate-500">{{ __('portal.services.description') }}</p>
                </div>
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800">
                    <span class="size-2 rounded-full bg-emerald-600"></span>
                    {{ __('portal.services.operational_active') }}
                </span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @php
            $districtOptions = collect(['' => __('portal.services.all_districts')])
                ->merge($districts->mapWithKeys(fn ($district) => [$district => $district]))
                ->all();
        @endphp
        <form id="servicesFilterForm" method="GET" action="{{ route('public.services') }}" data-ajax-form data-ajax-targets="#servicesFacilitiesList,#servicesRoutesCount,#servicesRoutesGrid,#servicesMapPayload" class="relative z-20 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_14rem_auto]">
            <label class="relative block">
                <span class="sr-only">{{ __('portal.services.search_label') }}</span>
                <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                <input name="search" value="{{ $filters['search'] }}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm outline-none placeholder:text-slate-400 focus:border-civic-500 focus:bg-white focus:ring-4 focus:ring-civic-100" placeholder="{{ __('portal.services.search_placeholder') }}">
            </label>
            <x-public.select-filter
                name="district"
                :value="$filters['district']"
                :options="$districtOptions"
                :placeholder="__('portal.services.all_districts')"
                :auto-submit="true"
                height-class="h-11"
            />
            <button class="min-h-11 rounded-xl bg-civic-700 px-5 text-sm font-bold text-white hover:bg-civic-800">{{ __('portal.common.apply') }}</button>
        </form>

        <div class="mt-6 grid gap-6 lg:grid-cols-[22rem_1fr]">
            <div id="servicesFacilitiesList" data-ajax-zone="services-facilities" data-ajax-targets="#servicesFacilitiesList,#servicesRoutesCount,#servicesRoutesGrid,#servicesMapPayload" class="order-2 space-y-3 lg:order-1">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-xl font-extrabold text-slate-950">{{ __('portal.services.facilities') }}</h2>
                    <span class="text-sm text-slate-500">{{ $facilities->total() }} {{ __('portal.common.locations') }}</span>
                </div>
                @forelse ($facilities as $facility)
                    <article class="portal-interactive-card overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        @if ($facility->foto_url)
                            <a href="{{ route('public.facilities.show', $facility) }}" class="block aspect-[16/8] overflow-hidden bg-slate-100">
                                <img src="{{ $facility->foto_url }}" alt="{{ $facility->nama_tps3r }}" class="h-full w-full object-cover transition duration-300 hover:scale-[1.03]">
                            </a>
                        @endif
                        <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-civic-700">{{ $facility->kode_tps3r ?: __('portal.services.facilities') }}</p>
                                <h3 class="mt-1 font-display text-base font-extrabold text-slate-950">
                                    <a href="{{ route('public.facilities.show', $facility) }}" class="hover:text-civic-700">{{ $facility->nama_tps3r }}</a>
                                </h3>
                            </div>
                            @if ($facility->latitude && $facility->longitude)
                                <button type="button" data-focus-facility="{{ $facility->id }}" class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-civic-100 text-civic-700 hover:bg-civic-200" aria-label="{{ __('portal.services.show_on_map', ['name' => $facility->nama_tps3r]) }}">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                </button>
                            @endif
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-500">{{ $facility->deskripsi ?: ($facility->alamat ?: __('portal.services.facility_fallback')) }}</p>
                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                            @if ($facility->kecamatan)<span class="rounded-full bg-slate-100 px-2.5 py-1">{{ __('portal.common.district_short') }} {{ $facility->kecamatan }}</span>@endif
                            @if ($facility->kapasitas_harian_kg)<span class="rounded-full bg-civic-50 px-2.5 py-1 text-civic-700">{{ number_format($facility->kapasitas_harian_kg, 0, ',', '.') }} {{ __('portal.common.per_day') }}</span>@endif
                        </div>
                        <a href="{{ route('public.facilities.show', $facility) }}" class="mt-4 inline-flex min-h-10 items-center text-sm font-bold text-civic-700 hover:text-civic-900">{{ __('portal.services.view_profile') }}</a>
                        </div>
                    </article>
                @empty
                    <x-public.empty-state :title="__('portal.services.empty_title')" :description="__('portal.services.empty_description')" />
                @endforelse
                <div>{{ $facilities->links() }}</div>
            </div>

            <div class="order-1 lg:order-2">
                <div class="sticky top-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-display text-lg font-extrabold text-slate-950">{{ __('portal.services.map_title') }}</h2>
                            <p class="mt-1 text-xs text-slate-500">{{ __('portal.services.map_description') }}</p>
                        </div>
                        <div class="flex gap-3 text-xs font-bold">
                            <span class="inline-flex items-center gap-1.5 text-civic-700"><span class="size-2.5 rounded-full bg-civic-600"></span> TPS3R</span>
                            <span class="inline-flex items-center gap-1.5 text-sky-700"><span class="size-2.5 rounded-full bg-sky-500"></span> Rute</span>
                        </div>
                    </div>
                    <div id="publicServicesMap" class="madiun-maplibre h-[28rem] w-full sm:h-[36rem]"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8" data-scroll-fade>
        <div class="rounded-3xl bg-civic-950 p-6 text-white sm:p-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wider text-civic-300">{{ __('portal.services.scheduled_routes') }}</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold">{{ __('portal.services.active_areas') }}</h2>
                </div>
                <span id="servicesRoutesCount" class="text-sm font-bold text-civic-100">{{ __('portal.services.route_count', ['count' => $routes->count()]) }}</span>
            </div>
            <div id="servicesRoutesGrid" class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($routes as $route)
                    <article class="rounded-2xl border border-white/10 bg-white/8 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-xs font-bold text-civic-300">{{ $route->route_code }}</span>
                            <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-bold">{{ $route->schedule_day }}</span>
                        </div>
                        <h3 class="mt-3 font-display font-extrabold">{{ $route->route_name }}</h3>
                        <p class="mt-2 text-sm text-civic-100/70">{{ $route->district }}{{ $route->subdistrict ? ' · '.$route->subdistrict : '' }}</p>
                        <p class="mt-4 text-xs font-semibold text-civic-100">{{ substr((string) $route->start_time, 0, 5) }}–{{ substr((string) $route->end_time, 0, 5) }} WIB</p>
                    </article>
                @empty
                    <p class="text-sm text-civic-100/70">{{ __('portal.services.empty_routes') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    <x-public.action-band />
@endsection

@push('scripts')
    @php
        $facilityMapData = $facilities->getCollection()->map(fn ($facility) => [
            'id' => $facility->id,
            'name' => $facility->nama_tps3r,
            'district' => $facility->kecamatan,
            'latitude' => $facility->latitude,
            'longitude' => $facility->longitude,
        ])->values();
        $routeMapData = $routes->map(fn ($route) => [
            'id' => $route->id,
            'name' => $route->route_name,
            'latitude' => $route->latitude,
            'longitude' => $route->longitude,
            'waypoints' => $route->waypoints,
        ])->values();
    @endphp
    <script id="servicesMapPayload" type="application/json">@json(['facilities' => $facilityMapData, 'routes' => $routeMapData])</script>
    <script>
        (() => {
            const readPayload = () => {
                const payloadEl = document.getElementById('servicesMapPayload');
                if (!payloadEl) {
                    return { facilities: [], routes: [] };
                }

                try {
                    return JSON.parse(payloadEl.textContent || '{}');
                } catch {
                    return { facilities: [], routes: [] };
                }
            };

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const initialize = () => {
                const container = document.getElementById('publicServicesMap');
                if (!container || !window.MadiunMapLibre || container.dataset.ready === 'true') return;
                container.dataset.ready = 'true';

                const map = window.MadiunMapLibre.createMap({
                    container: 'publicServicesMap',
                    center: [-7.6298, 111.5239],
                    zoom: 12.5,
                });

                const markers = new Map();
                let renderedRoutes = [];
                let mapReady = false;

                const renderMapData = (payload) => {
                    markers.forEach((marker) => marker.remove());
                    markers.clear();

                    renderedRoutes.forEach(({ sourceId, layerId }) => {
                        if (map.getLayer(layerId)) {
                            map.removeLayer(layerId);
                        }
                        if (map.getSource(sourceId)) {
                            map.removeSource(sourceId);
                        }
                    });
                    renderedRoutes = [];

                    const points = [];
                    const facilities = Array.isArray(payload.facilities) ? payload.facilities : [];
                    const routes = Array.isArray(payload.routes) ? payload.routes : [];

                    facilities.forEach((facility) => {
                        if (!Number.isFinite(Number(facility.latitude)) || !Number.isFinite(Number(facility.longitude))) return;
                        const position = [Number(facility.latitude), Number(facility.longitude)];
                        points.push(position);
                        const marker = window.MadiunMapLibre.createMarker(map, {
                            position,
                            color: '#046A58',
                            size: 18,
                            ariaLabel: `Lokasi ${facility.name}`,
                            popupHtml: `<strong>${escapeHtml(facility.name)}</strong><br><span>${escapeHtml(facility.district || @js(__('portal.common.city')))}</span>`,
                        });
                        markers.set(String(facility.id), marker);
                    });

                    routes.forEach((route) => {
                        const routePoints = Array.isArray(route.waypoints) ? [...route.waypoints] : [];
                        if (Number.isFinite(Number(route.latitude)) && Number.isFinite(Number(route.longitude))) {
                            routePoints.unshift([Number(route.latitude), Number(route.longitude)]);
                        }
                        const validPoints = window.MadiunMapLibre.validLatLngs(routePoints);
                        if (validPoints.length > 1) {
                            points.push(...validPoints);
                            const sourceId = `public-route-source-${route.id}`;
                            const layerId = `public-route-layer-${route.id}`;
                            window.MadiunMapLibre.upsertLine(map, {
                                sourceId,
                                layerId,
                                points: validPoints,
                                color: '#0284C7',
                                width: 4,
                            });
                            renderedRoutes.push({ sourceId, layerId });
                        }
                    });

                    if (points.length > 0) {
                        window.MadiunMapLibre.fitToPoints(map, points, { padding: 56, maxZoom: 14 });
                    }
                };

                map.on('load', () => {
                    mapReady = true;
                    renderMapData(readPayload());
                });

                document.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-focus-facility]');
                    if (!button) return;
                    const marker = markers.get(button.dataset.focusFacility);
                    if (!marker) return;
                    map.flyTo({ center: marker.getLngLat(), zoom: 15 });
                    marker.togglePopup();
                });

                document.addEventListener('madiun:ajax-updated', () => {
                    if (mapReady && document.getElementById('servicesMapPayload')) {
                        renderMapData(readPayload());
                    }
                });
            };

            if (window.MadiunMapLibre) initialize();
            else window.addEventListener('madiun:maplibre-ready', initialize, { once: true });
        })();
    </script>
@endpush
