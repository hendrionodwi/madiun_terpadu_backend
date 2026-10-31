<x-layouts::app :title="__('Penugasan Wilayah & Rute Petugas - Admin DLH')">
    <div class="civic-page">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Penugasan & Rute</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Penugasan Wilayah & Rute Operasional</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Atur rute tetap pengangkutan sampah dan jadwalkan penugasan armada petugas lapangan Kota Madiun.</p>
            </div>
            <div>
                <a href="{{ route('dashboard.admin.routes.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm hover:shadow">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Penugasan Rute Baru</span>
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Peta Monitoring Sebaran Rute & Armada (MapLibre GL JS) -->
        <div class="civic-card flex flex-col overflow-hidden p-4">
            <div class="mb-3 flex shrink-0 flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <svg class="size-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                        Peta Navigasi Jalur & Sebaran Armada Kota Madiun (MapLibre OSM)
                    </h2>
                    <p class="text-xs text-zinc-500">Peta interaktif pemantauan seluruh rute tetap dan pergerakan armada penjemputan sampah. Klik tombol <b>'Jalur'</b> pada tabel di bawah untuk melihat detail rute tertentu.</p>
                </div>
                <div class="flex items-center gap-2.5 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-semibold bg-emerald-50 dark:bg-emerald-950/40 px-2 py-1 rounded-lg border border-emerald-200 dark:border-emerald-800 text-[11px]">
                        <span class="w-3.5 h-1 bg-emerald-500 rounded-full"></span> Jalur Rute Tetap
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-blue-700 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-950/40 px-2 py-1 rounded-lg border border-blue-200 dark:border-blue-800 text-[11px]">
                        <span class="size-2.5 rounded-full bg-blue-500 ring-2 ring-blue-200"></span> Truk Berlangsung
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-semibold bg-amber-50 dark:bg-amber-950/40 px-2 py-1 rounded-lg border border-amber-200 dark:border-amber-800 text-[11px]">
                        <span class="size-2.5 rounded-full bg-amber-500 ring-2 ring-amber-200"></span> Truk Terjadwal
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-semibold bg-emerald-50 dark:bg-emerald-950/40 px-2 py-1 rounded-lg border border-emerald-200 dark:border-emerald-800 text-[11px]">
                        <span class="size-2.5 rounded-full bg-emerald-600 ring-2 ring-emerald-200"></span> Truk Selesai
                    </span>
                </div>
            </div>
            <div id="adminRouteMap" class="madiun-maplibre h-[24rem] w-full sm:h-[32.5rem]"></div>
        </div>

        <div class="grid gap-6">
                <div class="civic-card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">
                        <div>
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Rute Tetap Aktif Kota Madiun</h2>
                            <p class="text-xs text-zinc-500">Daftar jalur pengangkutan sampah harian.</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                            {{ $routes->total() }} Rute Terdaftar
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[64rem] text-left text-xs">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 font-semibold uppercase">
                                    <th class="py-3 px-4">Kode & Jalur Rute</th>
                                    <th class="py-3 px-4">Wilayah</th>
                                    <th class="py-3 px-4">Petugas Lapangan</th>
                                    <th class="py-3 px-4">Hari & Jam</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse ($routes as $route)
                                    <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $route->route_code }}</span>
                                                <button type="button" onclick="focusRouteOnMap({{ $route->id }})" class="px-2 py-0.5 rounded text-[10px] bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-950 dark:hover:bg-emerald-900 text-emerald-800 dark:text-emerald-300 font-semibold transition inline-flex items-center gap-1" title="Tampilkan jalur rute ini di peta">
                                                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    Jalur
                                                </button>
                                            </div>
                                            <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $route->route_name }}</p>
                                            <div class="flex items-center gap-1.5 text-[10px] text-zinc-500 mt-1 flex-wrap">
                                                <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"><flux:icon.flag class="size-3" /> {{ $route->start_location ?? 'Depo DLH' }}</span>
                                                <span class="text-zinc-400 font-bold">➔</span>
                                                <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-1.5 py-0.5 font-medium text-blue-700 dark:bg-blue-950/60 dark:text-blue-400"><flux:icon.building-office-2 class="size-3" /> {{ $route->end_facility ?? 'TPS3R Kota' }}</span>
                                            </div>
                                            @if ($route->notes)
                                                <p class="text-[11px] text-zinc-400 italic mt-0.5">{{ $route->notes }}</p>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $route->district }}</span>
                                            <p class="text-[11px] text-zinc-400">{{ $route->subdistrict ?? 'Semua kelurahan' }}</p>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $route->petugas->name ?? 'Belum Ditugaskan' }}</span>
                                            <p class="text-[11px] text-zinc-400">{{ $route->petugas->petugasLapanganProfile->plat_nomor ?? '-' }}</p>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $route->schedule_day }}</span>
                                            <p class="text-[11px] text-zinc-400">{{ $route->start_time }} - {{ $route->end_time }}</p>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $route->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-800' }}">
                                                {{ $route->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('dashboard.admin.routes.edit', $route->id) }}" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 transition inline-block">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('dashboard.admin.routes.destroy', $route->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rute {{ $route->route_code }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-800 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-zinc-400 text-xs">
                                            Belum ada rute tetap. Silakan tambahkan melalui form di samping.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($routes->hasPages())
                        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                            {{ $routes->links() }}
                        </div>
                    @endif
                </div>

                <!-- Status Sesi Perjalanan Armada Hari Ini (Depo ➔ TPS3R) -->
                <div class="civic-card p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <span class="inline-block size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Status Operasional Sesi Armada Hari Ini (Depo ➔ TPS3R)
                            </h3>
                            <p class="text-xs text-zinc-500">Pemantauan riil keberangkatan dari Depo DLH hingga penyelesaian di TPS3R berdasarkan interaksi aplikasi mobile petugas.</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            {{ $routeSessions->count() }} Sesi Tercatat Hari Ini
                        </span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        @forelse ($routeSessions as $session)
                            <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 shadow-sm text-xs">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded">{{ $session->route_code }}</span>
                                    @php
                                        $badgeClasses = match($session->status) {
                                            'berlangsung' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border-sky-300',
                                            'selesai' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300',
                                            default => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClasses }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </div>
                                <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $session->routeAssignment->route_name ?? 'Rute Operasional' }}</p>
                                <div class="mt-2 space-y-1 text-[11px] text-zinc-600 dark:text-zinc-400">
                                    <div class="flex items-center justify-between">
                                        <span>Petugas:</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $session->petugas->name ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Waktu Mulai:</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $session->waktu_mulai ? \Carbon\Carbon::parse($session->waktu_mulai)->format('H:i') . ' WIB' : 'Belum Berangkat' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Waktu Selesai:</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $session->waktu_selesai ? \Carbon\Carbon::parse($session->waktu_selesai)->format('H:i') . ' WIB' : 'Belum Selesai' }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2 border-t border-zinc-100 dark:border-zinc-700/60 flex items-center justify-between text-[10px] text-zinc-400">
                                    <span class="inline-flex items-center gap-1"><flux:icon.flag class="size-3" /> {{ $session->start_location ?? 'Depo' }}</span>
                                    <span>➔</span>
                                    <span class="inline-flex items-center gap-1"><flux:icon.building-office-2 class="size-3" /> {{ $session->end_facility ?? 'TPS3R' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-6 text-center text-zinc-400 text-xs">
                                Belum ada armada yang mengaktifkan sesi penjemputan hari ini. Petugas lapangan dapat menekan tombol <b>[Mulai Penjemputan di Depo]</b> pada aplikasi mobile.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Jadwal Operasional Penjemputan Terkini -->
                <div class="civic-card p-5">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-1">Jadwal Penjemputan Sampah Warga</h3>
                    <p class="text-xs text-zinc-500 mb-3">Monitoring antrean penjemputan door-to-door yang diajukan oleh warga (kuning = menunggu, hijau = selesai ditimbang).</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                        @forelse ($schedules as $sch)
                            <div class="p-3 rounded-xl border border-zinc-100 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/40 text-xs">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $sch->id_wilayah_rute }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sch->status === 'berlangsung' ? 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300' : ($sch->status === 'selesai' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300') }}">
                                        {{ ucfirst($sch->status) }}
                                    </span>
                                </div>
                                <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $sch->nama_wilayah_rute ?? 'Jalur Wilayah' }}</p>
                                <p class="text-zinc-500 mt-0.5">{{ \Carbon\Carbon::parse($sch->tanggal_penjemputan)->translatedFormat('d M Y') }} • {{ $sch->waktu_mulai }}-{{ $sch->waktu_selesai }}</p>
                                <p class="text-[11px] text-zinc-400 mt-1">Petugas: {{ $sch->petugas->name ?? '-' }}</p>
                            </div>
                        @empty
                            <div class="col-span-full py-4 text-center text-zinc-400 text-xs">Belum ada permintaan penjemputan sampah dari warga.</div>
                        @endforelse
                    </div>
                </div>
        </div>
    </div>

    <!-- MapLibre GL JS untuk Monitoring Rute & Armada Kota Madiun -->
    <script>
    (function () {
        let adminMap = null;
        let savedRouteLayers = {};

        // Data rute & jadwal armada dari backend
        const routesData = @json($routes->items());
        const schedulesData = @json($schedules);

        function ensureMapLibre(callback) {
            if (window.MadiunMapLibre) {
                callback();
                return;
            }

            window.addEventListener('madiun:maplibre-ready', callback, { once: true });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        let isInitializing = false;

        function cleanupExistingMap() {
            if (adminMap) {
                try {
                    adminMap.stop();
                    adminMap.off();
                    adminMap.remove();
                } catch (e) {}
                adminMap = null;
            }
            const mapContainer = document.getElementById('adminRouteMap');
            if (mapContainer) {
                const freshContainer = mapContainer.cloneNode(false);
                freshContainer.innerHTML = '';
                mapContainer.replaceWith(freshContainer);
            }
        }

        function initMap() {
            const mapContainer = document.getElementById('adminRouteMap');
            if (!mapContainer) return;

            if (!window.MadiunMapLibre) {
                ensureMapLibre(initMap);
                return;
            }

            if (isInitializing) return;
            isInitializing = true;

            cleanupExistingMap();

            try {
                adminMap = window.MadiunMapLibre.createMap({
                    container: 'adminRouteMap',
                    center: [-7.6298, 111.5239],
                    zoom: 13,
                });
            } catch (err) {
                console.error('Error initializing adminRouteMap:', err);
                isInitializing = false;
                return;
            }
            isInitializing = false;

            adminMap.on('load', function () {
            savedRouteLayers = {};
            const allPoints = [];

            // Plot Rute Tetap yang sudah tersimpan di database beserta status sesi perjalanan hari ini
            routesData.forEach(function (r) {
                const session = r.today_session;
                const sessionStatus = session ? session.status : 'terjadwal';

                let startColor = '#f59e0b'; // Default: Terjadwal (Kuning/Amber)
                let endColor = '#f59e0b';   // Default: Terjadwal (Kuning/Amber)
                let polyColor = '#059669';

                if (sessionStatus === 'berlangsung') {
                    startColor = '#0284c7'; // Biru (Aktif berangkat dari Depo)
                    endColor = '#f59e0b';   // Kuning (Menunggu kedatangan di TPS3R)
                    polyColor = '#0284c7';  // Garis rute membiru
                } else if (sessionStatus === 'selesai') {
                    startColor = '#16a34a'; // Hijau (Selesai)
                    endColor = '#16a34a';   // Hijau (Selesai di TPS3R)
                    polyColor = '#16a34a';  // Garis rute hijau
                }

                if (r.waypoints && Array.isArray(r.waypoints) && r.waypoints.length > 1) {
                    const validPoints = r.waypoints
                        .map(pt => [parseFloat(pt[0]), parseFloat(pt[1])])
                        .filter(pt => !isNaN(pt[0]) && !isNaN(pt[1]));

                    if (validPoints.length > 1) {
                        const sourceId = `route-source-${r.id}`;
                        const layerId = `route-line-${r.id}`;
                        const routePopup = `
                            <div style="font-size:12px; line-height:1.4;">
                                <strong style="color:#047857;">${escapeHtml(r.route_code)}</strong><br>
                                <strong>${escapeHtml(r.route_name)}</strong><br>
                                <span style="color:#64748b;">Wilayah: ${escapeHtml(r.district)}</span><br>
                                <span>Petugas: ${escapeHtml(r.petugas ? r.petugas.name : '-')}</span><br>
                                <span style="font-weight:700; color:${polyColor};">Status Sesi Hari Ini: ${sessionStatus.toUpperCase()}</span>
                            </div>
                        `;

                        window.MadiunMapLibre.upsertLine(adminMap, {
                            sourceId,
                            layerId,
                            points: validPoints,
                            color: polyColor,
                            width: 5,
                            opacity: 0.85,
                        });

                        adminMap.on('click', layerId, function (event) {
                            new window.MadiunMapLibre.Popup({ maxWidth: '320px' })
                                .setLngLat(event.lngLat)
                                .setHTML(routePopup)
                                .addTo(adminMap);
                        });
                        adminMap.on('mouseenter', layerId, () => { adminMap.getCanvas().style.cursor = 'pointer'; });
                        adminMap.on('mouseleave', layerId, () => { adminMap.getCanvas().style.cursor = ''; });

                        savedRouteLayers[r.id] = {
                            points: validPoints,
                            popupHtml: routePopup,
                            popupPoint: validPoints[Math.floor(validPoints.length / 2)],
                        };
                        allPoints.push(...validPoints);

                        // Titik Awal: Depo DLH
                        const startPt = validPoints[0];
                        window.MadiunMapLibre.createMarker(adminMap, {
                            position: startPt,
                            color: startColor,
                            size: 16,
                            ariaLabel: `Titik mulai ${r.route_code}`,
                            popupHtml: `
                            <div style="font-size:12px; line-height:1.4;">
                                <strong>Titik Mulai (Depo)</strong><br>
                                <b>${escapeHtml(r.start_location || 'Depo DLH')}</b><br>
                                <span style="color:#64748b;">Rute: ${escapeHtml(r.route_code)}</span><br>
                                <span>Status: <b style="color:${startColor};">${sessionStatus.toUpperCase()}</b></span><br>
                                <small style="color:#64748b;">Mulai: ${escapeHtml(session && session.waktu_mulai ? session.waktu_mulai.substring(0, 5) + ' WIB' : 'Belum Berangkat')}</small>
                            </div>
                            `,
                        });

                        // Titik Akhir: TPS3R Tujuan
                        const endPt = validPoints[validPoints.length - 1];
                        window.MadiunMapLibre.createMarker(adminMap, {
                            position: endPt,
                            color: endColor,
                            size: 16,
                            ariaLabel: `Titik selesai ${r.route_code}`,
                            popupHtml: `
                            <div style="font-size:12px; line-height:1.4;">
                                <strong>Titik Selesai (TPS3R)</strong><br>
                                <b>${escapeHtml(r.end_facility || 'TPS3R')}</b><br>
                                <span style="color:#64748b;">Rute: ${escapeHtml(r.route_code)}</span><br>
                                <span>Status: <b style="color:${endColor};">${sessionStatus.toUpperCase()}</b></span><br>
                                <small style="color:#64748b;">Selesai: ${escapeHtml(session && session.waktu_selesai ? session.waktu_selesai.substring(0, 5) + ' WIB' : 'Menunggu Penyelesaian')}</small>
                            </div>
                            `,
                        });
                    }
                } else if (r.latitude && r.longitude) {
                    const lat = parseFloat(r.latitude);
                    const lng = parseFloat(r.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const popupHtml = `
                            <div style="font-size:12px; line-height:1.4;">
                                <strong style="color:#047857;">${escapeHtml(r.route_code)}</strong><br>
                                <strong>${escapeHtml(r.route_name)}</strong><br>
                                <span style="color:#64748b;">Wilayah: ${escapeHtml(r.district)}</span><br>
                                <span>Petugas: ${escapeHtml(r.petugas ? r.petugas.name : 'Belum Ditugaskan')}</span><br>
                                <span style="font-weight:700; color:${polyColor};">Status Sesi Hari Ini: ${sessionStatus.toUpperCase()}</span>
                            </div>
                        `;
                        const marker = window.MadiunMapLibre.createMarker(adminMap, {
                            position: [lat, lng],
                            color: polyColor,
                            size: 16,
                            ariaLabel: `Lokasi rute ${r.route_code}`,
                            popupHtml,
                        });
                        savedRouteLayers[r.id] = { points: [[lat, lng]], popupHtml, popupPoint: [lat, lng], marker };
                        allPoints.push([lat, lng]);
                    }
                }
            });

            // Plot Jadwal Armada Truk Berjalan
            schedulesData.forEach(function (s) {
                if (s.latitude && s.longitude) {
                    const slat = parseFloat(s.latitude);
                    const slng = parseFloat(s.longitude);
                    if (!isNaN(slat) && !isNaN(slng)) {
                        const color = s.status === 'berlangsung' ? '#0284c7' : (s.status === 'selesai' ? '#16a34a' : '#f59e0b');
                        window.MadiunMapLibre.createMarker(adminMap, {
                            position: [slat, slng],
                            color,
                            size: 14,
                            ariaLabel: `Armada ${s.nama_wilayah_rute || s.id_wilayah_rute}`,
                            popupHtml: `
                            <div style="font-size:12px; line-height:1.4;">
                                <strong>${escapeHtml(s.nama_wilayah_rute || s.id_wilayah_rute)}</strong><br>
                                <span>Status: <b style="color:${color};">${s.status.toUpperCase()}</b></span><br>
                                <small style="color:#64748b;">${escapeHtml(s.tanggal_penjemputan)} • ${escapeHtml(s.waktu_mulai)}-${escapeHtml(s.waktu_selesai)}</small><br>
                                <small>Petugas: ${escapeHtml(s.petugas ? s.petugas.name : '-')}</small>
                            </div>
                            `,
                        });
                        allPoints.push([slat, slng]);
                    }
                }
            });

            // Jika ada rute tersimpan, sesuaikan view peta
            if (allPoints.length > 0) {
                try {
                    window.MadiunMapLibre.fitToPoints(adminMap, allPoints, { padding: 50, maxZoom: 15 });
                } catch (e) {}
            }
            });
        }

        // Global function untuk tombol 'Jalur' pada tabel rute
        window.focusRouteOnMap = function (routeId) {
            const layer = savedRouteLayers[routeId];
            if (layer && adminMap) {
                window.MadiunMapLibre.fitToPoints(adminMap, layer.points, {
                    padding: 48,
                    maxZoom: 15,
                    duration: 500,
                });
                new window.MadiunMapLibre.Popup({ maxWidth: '320px' })
                    .setLngLat(window.MadiunMapLibre.toLngLat(layer.popupPoint))
                    .setHTML(layer.popupHtml)
                    .addTo(adminMap);
            } else {
                const target = routesData.find(r => r.id === routeId);
                if (target && target.latitude && target.longitude && adminMap) {
                    adminMap.flyTo({
                        center: window.MadiunMapLibre.toLngLat([target.latitude, target.longitude]),
                        zoom: 15,
                    });
                }
            }
        };

        let mapInitialized = false;

        function start() {
            if (mapInitialized && adminMap) return;
            mapInitialized = true;
            ensureMapLibre(initMap);
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(start, 50);
        } else {
            document.addEventListener('DOMContentLoaded', start, { once: true });
        }

        document.addEventListener('livewire:navigated', function () {
            mapInitialized = false;
            setTimeout(start, 80);
        });

        document.addEventListener('livewire:navigating', function () {
            cleanupExistingMap();
            mapInitialized = false;
        });

        window.addEventListener('resize', function () {
            if (adminMap) adminMap.resize();
        });
    })();
    </script>
</x-layouts::app>
