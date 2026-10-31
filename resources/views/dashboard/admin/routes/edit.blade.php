<x-layouts::app :title="__('Edit Penugasan Rute & Lintasan Peta - Admin DLH')">
    <div class="civic-page">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <a href="{{ route('dashboard.admin.routes') }}" class="hover:text-emerald-600 transition">Penugasan & Rute</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Edit Rute {{ $route->route_code }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">
                        Edit Penugasan Rute: <span class="font-mono text-emerald-700 dark:text-emerald-400">{{ $route->route_code }}</span>
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $route->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300' }}">
                        {{ $route->is_active ? 'Aktif Operasional' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Perbarui detail penugasan armada dan modifikasi jalur lintasan peta secara interaktif.</p>
            </div>
            <div>
                <a href="{{ route('dashboard.admin.routes') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar Rute</span>
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-700 p-4 text-xs text-red-800 dark:text-red-200">
                <p class="font-bold mb-1">Terdapat kesalahan pada input:</p>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(24rem,1fr)_minmax(0,1.5fr)]">
            <!-- Form Edit Detail Rute -->
            <div class="civic-card p-5 flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-1">Informasi Rute & Armada</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">Ubah penanggung jawab lapangan, jadwal harian, atau wilayah tugas.</p>

                    <form id="editRouteForm" method="POST" action="{{ route('dashboard.admin.routes.update', $route->id) }}" class="space-y-3.5 text-xs">
                        @csrf
                        @method('PATCH')

                        <!-- Hidden Inputs Koordinat & Titik Jalur (Waypoints) -->
                        <input type="hidden" id="route_lat" name="latitude" value="{{ old('latitude', $route->latitude) }}">
                        <input type="hidden" id="route_lng" name="longitude" value="{{ old('longitude', $route->longitude) }}">
                        <input type="hidden" id="route_waypoints" name="waypoints" value="{{ old('waypoints', json_encode($route->waypoints ?? [])) }}">

                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Petugas Lapangan Bertugas *</label>
                            <select name="petugas_id" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-emerald-500">
                                @foreach ($petugasList as $p)
                                    <option value="{{ $p->id }}" {{ (string) old('petugas_id', $route->petugas_id) === (string) $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->petugasLapanganProfile->plat_nomor ?? 'Armada Dump Truck' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div>
                                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kode Rute *</label>
                                <input type="text" name="route_code" value="{{ old('route_code', $route->route_code) }}" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kecamatan *</label>
                                <select id="district_select" name="district" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                    @forelse ($districts ?? [] as $d)
                                        <option value="{{ $d }}" {{ old('district', $route->district) === $d ? 'selected' : '' }}>{{ $d }}</option>
                                    @empty
                                        <option value="{{ $route->district }}" selected>{{ $route->district }}</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama / Jalur Rute *</label>
                            <input type="text" name="route_name" value="{{ old('route_name', $route->route_name) }}" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 flex items-center gap-1.5 font-semibold text-zinc-700 dark:text-zinc-300"><flux:icon.flag class="size-4" /> Depo Awal</label>
                                <input type="text" name="start_location" value="{{ old('start_location', $route->start_location) }}" placeholder="Contoh: Depo Truk Kartoharjo Dr3" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            </div>
                            <div class="relative" x-data="{
                                open: false,
                                search: {{ \Illuminate\Support\Js::from(old('end_facility', $route->end_facility ?? '')) }},
                                facilities: {{ \Illuminate\Support\Js::from($tps3rFacilities ?? []) }},
                                get filtered() {
                                    if (!this.search || !this.search.trim()) {
                                        return this.facilities;
                                    }
                                    const q = this.search.toLowerCase().trim();
                                    return this.facilities.filter(f =>
                                        (f.name && f.name.toLowerCase().includes(q)) ||
                                        (f.location && f.location.toLowerCase().includes(q)) ||
                                        (f.district && f.district.toLowerCase().includes(q)) ||
                                        (f.code && f.code.toLowerCase().includes(q))
                                    );
                                },
                                select(name) {
                                    this.search = name;
                                    this.open = false;
                                }
                            }" @click.outside="open = false">
                                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5"><flux:icon.building-office-2 class="size-4" /> Tujuan Akhir</span>
                                    <span class="text-[11px] font-normal text-emerald-600 dark:text-emerald-400">Pilih TPS3R / Ketik Pencarian</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        name="end_facility"
                                        x-model="search"
                                        @focus="open = true"
                                        @input="open = true"
                                        @keydown.escape="open = false"
                                        placeholder="Ketik untuk mencari TPS3R / TPA..."
                                        autocomplete="off"
                                        class="w-full pl-3 pr-16 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-sm"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-1">
                                        <button
                                            type="button"
                                            x-show="search && search.length > 0"
                                            @click="search = ''; open = true"
                                            class="p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition"
                                            title="Kosongkan"
                                            tabindex="-1"
                                        >
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition"
                                            title="Tampilkan daftar TPS3R"
                                            tabindex="-1"
                                        >
                                            <svg class="size-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Dropdown Menu Popover -->
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1"
                                    class="absolute left-0 right-0 z-50 mt-1.5 max-h-64 overflow-y-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-xl py-1 text-xs divide-y divide-zinc-100 dark:divide-zinc-800/60"
                                    style="display: none;"
                                >
                                    <template x-if="filtered.length > 0">
                                        <div>
                                            <template x-for="item in filtered" :key="item.name">
                                                <button
                                                    type="button"
                                                    @click="select(item.name)"
                                                    class="w-full text-left px-3 py-2 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition flex items-center justify-between gap-2"
                                                    :class="{ 'bg-emerald-50/70 dark:bg-emerald-950/30': search && search.toLowerCase() === item.name.toLowerCase() }"
                                                >
                                                    <div class="min-w-0">
                                                        <div class="font-bold text-zinc-900 dark:text-zinc-100 truncate flex items-center gap-1.5">
                                                            <span x-text="item.name"></span>
                                                            <span x-show="search && search.toLowerCase() === item.name.toLowerCase()" class="text-emerald-600 dark:text-emerald-400 font-bold">✓</span>
                                                        </div>
                                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate" x-text="item.location"></div>
                                                    </div>
                                                    <span
                                                        class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold"
                                                        :class="item.tag === 'TPA Akhir' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300'"
                                                        x-text="item.tag"
                                                    ></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="filtered.length === 0">
                                        <div class="px-3 py-2.5 text-center text-zinc-500 dark:text-zinc-400">
                                            <p class="font-semibold text-zinc-700 dark:text-zinc-300">Tidak ada fasilitas terdaftar yang cocok</p>
                                            <p class="text-[11px] mt-0.5">Teks "<span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="search"></span>" akan disimpan sebagai tujuan kustom.</p>
                                        </div>
                                    </template>

                                    <!-- Quick Select Option jika mengetik custom text -->
                                    <div x-show="search && search.trim() && !facilities.some(f => f.name.toLowerCase() === search.toLowerCase().trim())" class="p-2 bg-zinc-50 dark:bg-zinc-800/40">
                                        <button
                                            type="button"
                                            @click="open = false"
                                            class="w-full text-left px-2.5 py-1.5 rounded-lg border border-dashed border-emerald-300 dark:border-emerald-700 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition flex items-center gap-1.5"
                                        >
                                            <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span class="truncate">Gunakan tujuan kustom: <strong class="underline" x-text="search"></strong></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kelurahan / Titik Fokus Dilalui</label>
                            <input type="text" name="subdistrict" value="{{ old('subdistrict', $route->subdistrict) }}" placeholder="Contoh: Oro-Oro Ombo, Kartoharjo" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <div>
                                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Hari Operasional *</label>
                                <select name="schedule_day" required class="w-full px-2 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                    @foreach (['Setiap Hari', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                        <option value="{{ $day }}" {{ old('schedule_day', $route->schedule_day) === $day ? 'selected' : '' }}>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Jam Mulai *</label>
                                <input type="text" name="start_time" value="{{ old('start_time', $route->start_time) }}" placeholder="08:00" required class="w-full px-2 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Jam Selesai *</label>
                                <input type="text" name="end_time" value="{{ old('end_time', $route->end_time) }}" placeholder="12:00" required class="w-full px-2 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Status Keaktifan</label>
                            <select name="is_active" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                <option value="1" {{ old('is_active', $route->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Aktif Operasional</option>
                                <option value="0" {{ old('is_active', $route->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Nonaktif / Penugasan Ditangguhkan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan operasional jalur atau instruksi khusus..." class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">{{ old('notes', $route->notes) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                            <a href="{{ route('dashboard.admin.routes') }}" class="px-4 py-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold hover:bg-zinc-200 transition">
                                Batal
                            </a>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm flex items-center gap-1.5">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Perubahan Rute</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Peta Editor Lintasan Rute (MapLibre GL JS) -->
            <div class="civic-card flex h-full flex-col overflow-hidden p-4">
                <div class="mb-3 flex shrink-0 flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <svg class="size-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                            Editor Jalur Lintasan Rute (MapLibre OSM)
                        </h2>
                        <p class="text-xs text-zinc-500">Klik di peta untuk menambah titik belok jalur. Klik marker untuk melihat info urutan titik.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="routePointsCountBadge" class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            0 Titik Jalur
                        </span>
                        <button type="button" id="btnUndoWaypoint" title="Hapus titik terakhir" class="px-2.5 py-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 transition">
                            Undo
                        </button>
                        <button type="button" id="btnClearWaypoints" title="Reset semua titik rute" class="px-2.5 py-1 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 text-xs font-semibold text-red-700 dark:text-red-300 hover:bg-red-100 transition">
                            Reset
                        </button>
                        <button type="button" id="btnPresetRoute" title="Gunakan preset jalur kecamatan" class="px-2.5 py-1 rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 transition">
                            Preset
                        </button>
                        <button type="button" id="btnFocusRoute" title="Fokuskan tampilan ke seluruh rute" class="px-2.5 py-1 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/50 text-xs font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition">
                            Fokus
                        </button>
                    </div>
                </div>

                <div id="adminRouteEditMap" class="madiun-maplibre h-[24rem] w-full sm:h-[32.5rem]"></div>

                <div class="mt-3 flex items-center justify-between text-[11px] text-zinc-500 border-t border-zinc-100 dark:border-zinc-800 pt-2.5">
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-800 dark:text-emerald-300">
                            <span class="size-2.5 rounded-full bg-emerald-600 ring-2 ring-emerald-200"></span> Titik Awal (Depo)
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400">
                            <span class="size-2.5 rounded-full bg-emerald-400"></span> Titik Lintasan Armada
                        </span>
                    </div>
                    <p class="hidden sm:block text-zinc-400">MapLibre GL JS • Koordinat otomatis tersimpan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MapLibre GL JS untuk Editor Rute -->
    <script>
    (function () {
        let editMap = null;
        let routeWaypoints = [];
        let drawingPolyline = null;
        let waypointMarkers = [];

        // Data awal dari route yang sedang diedit
        const initialWaypointsRaw = @json($route->waypoints ?? []);
        const defaultLat = {{ (float) ($route->latitude ?: -7.6180) }};
        const defaultLng = {{ (float) ($route->longitude ?: 111.5300) }};

        if (Array.isArray(initialWaypointsRaw) && initialWaypointsRaw.length > 0) {
            routeWaypoints = JSON.parse(JSON.stringify(initialWaypointsRaw));
        } else if (defaultLat && defaultLng) {
            routeWaypoints = [[defaultLat, defaultLng]];
        }

        const districtPresets = {
            'Kartoharjo': [
                [-7.6180, 111.5300],
                [-7.6210, 111.5280],
                [-7.6240, 111.5260],
                [-7.6270, 111.5240],
                [-7.6298, 111.5239]
            ],
            'Taman': [
                [-7.6380, 111.5250],
                [-7.6350, 111.5240],
                [-7.6320, 111.5240],
                [-7.6298, 111.5239]
            ],
            'Manguharjo': [
                [-7.6250, 111.5120],
                [-7.6260, 111.5160],
                [-7.6275, 111.5190],
                [-7.6298, 111.5239]
            ]
        };

        function ensureMapLibre(callback) {
            if (window.MadiunMapLibre) {
                callback();
                return;
            }

            window.addEventListener('madiun:maplibre-ready', callback, { once: true });
        }

        function updateFormInputs() {
            const latInput = document.getElementById('route_lat');
            const lngInput = document.getElementById('route_lng');
            const wpInput = document.getElementById('route_waypoints');
            const badge = document.getElementById('routePointsCountBadge');

            if (routeWaypoints.length > 0) {
                const firstLat = parseFloat(routeWaypoints[0][0]);
                const firstLng = parseFloat(routeWaypoints[0][1]);
                if (latInput && !isNaN(firstLat)) latInput.value = firstLat.toFixed(6);
                if (lngInput && !isNaN(firstLng)) lngInput.value = firstLng.toFixed(6);
            }

            if (wpInput) wpInput.value = JSON.stringify(routeWaypoints);

            if (badge) {
                badge.textContent = routeWaypoints.length > 0
                    ? `${routeWaypoints.length} Titik Jalur`
                    : '0 Titik Jalur';
            }
        }

        function redrawWaypoints() {
            if (!editMap) return;

            if (!editMap.isStyleLoaded()) {
                updateFormInputs();
                return;
            }

            waypointMarkers.forEach(marker => marker.remove());
            waypointMarkers = [];
            if (drawingPolyline) {
                window.MadiunMapLibre.removeLine(editMap, drawingPolyline.sourceId, drawingPolyline.layerId);
                drawingPolyline = null;
            }

            if (routeWaypoints.length === 0) {
                updateFormInputs();
                return;
            }

            if (routeWaypoints.length > 1) {
                drawingPolyline = {
                    sourceId: 'draft-route-source',
                    layerId: 'draft-route-line',
                };

                window.MadiunMapLibre.upsertLine(editMap, {
                    ...drawingPolyline,
                    points: routeWaypoints,
                    color: '#059669',
                    width: 5,
                    opacity: 0.9,
                    dasharray: [2, 1.5],
                });
            }

            // Buat Marker untuk setiap titik jalur
            routeWaypoints.forEach((pt, index) => {
                const lat = parseFloat(pt[0]);
                const lng = parseFloat(pt[1]);
                if (isNaN(lat) || isNaN(lng)) return;

                const isStart = index === 0;
                const marker = window.MadiunMapLibre.createMarker(editMap, {
                    position: [lat, lng],
                    color: isStart ? '#047857' : '#10b981',
                    size: isStart ? 18 : 13,
                    ariaLabel: isStart ? 'Titik awal rute' : `Titik jalur ${index + 1}`,
                    popupHtml: `
                    <div style="font-size:12px; line-height:1.4;">
                        <b>${isStart ? 'Pos / Titik Awal' : `Titik Jalur #${index + 1}`}</b><br>
                        <small style="color:#64748b;">${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                    </div>
                    `,
                });

                waypointMarkers.push(marker);
            });

            updateFormInputs();
        }

        function focusMapToRoute() {
            if (!editMap) return;
            try {
                if (routeWaypoints.length > 0) {
                    window.MadiunMapLibre.fitToPoints(editMap, routeWaypoints, {
                        padding: 48,
                        maxZoom: 15,
                        duration: 0,
                    });
                } else {
                    editMap.jumpTo({
                        center: window.MadiunMapLibre.toLngLat([defaultLat, defaultLng]),
                        zoom: 14,
                    });
                }
            } catch (e) {
                console.warn('fitBounds warning:', e);
            }
        }

        let isInitializing = false;

        function cleanupExistingMap() {
            if (editMap) {
                try {
                    editMap.stop();
                    editMap.off();
                    editMap.remove();
                } catch (e) {}
                editMap = null;
            }
            const mapContainer = document.getElementById('adminRouteEditMap');
            if (mapContainer) {
                const freshContainer = mapContainer.cloneNode(false);
                freshContainer.innerHTML = '';
                mapContainer.replaceWith(freshContainer);
            }
        }

        function initMap() {
            const mapContainer = document.getElementById('adminRouteEditMap');
            if (!mapContainer) return;

            if (!window.MadiunMapLibre) {
                ensureMapLibre(initMap);
                return;
            }

            if (isInitializing) return;
            isInitializing = true;

            cleanupExistingMap();

            const initialCenter = routeWaypoints.length > 0 ? routeWaypoints[0] : [defaultLat, defaultLng];

            try {
                editMap = window.MadiunMapLibre.createMap({
                    container: 'adminRouteEditMap',
                    center: initialCenter,
                    zoom: 14,
                });

                editMap.on('load', function () {
                    redrawWaypoints();
                    focusMapToRoute();
                });

                editMap.on('click', function (event) {
                    const newPoint = [
                        parseFloat(event.lngLat.lat.toFixed(6)),
                        parseFloat(event.lngLat.lng.toFixed(6)),
                    ];
                    routeWaypoints.push(newPoint);
                    redrawWaypoints();
                });
            } catch (err) {
                console.error('Error initializing MapLibre map:', err);
            } finally {
                isInitializing = false;
            }
        }

        // Dropdown Kecamatan change listener
        const districtSelect = document.getElementById('district_select');
        if (districtSelect) {
            districtSelect.addEventListener('change', function () {
                const preset = districtPresets[this.value];
                if (preset && preset.length > 0 && editMap) {
                    editMap.flyTo({ center: window.MadiunMapLibre.toLngLat(preset[0]), zoom: 14 });
                } else if (editMap) {
                    editMap.flyTo({ center: window.MadiunMapLibre.toLngLat([-7.6298, 111.5239]), zoom: 14 });
                }
            });
        }

        // Tombol Undo
        const btnUndo = document.getElementById('btnUndoWaypoint');
        if (btnUndo) {
            btnUndo.addEventListener('click', function () {
                if (routeWaypoints.length > 0) {
                    routeWaypoints.pop();
                    redrawWaypoints();
                }
            });
        }

        // Tombol Reset
        const btnClear = document.getElementById('btnClearWaypoints');
        if (btnClear) {
            btnClear.addEventListener('click', function () {
                if (confirm('Apakah Anda yakin ingin mengosongkan seluruh titik lintasan rute ini?')) {
                    routeWaypoints = [];
                    redrawWaypoints();
                }
            });
        }

        // Tombol Preset
        const btnPreset = document.getElementById('btnPresetRoute');
        if (btnPreset) {
            btnPreset.addEventListener('click', function () {
                const dist = districtSelect ? districtSelect.value : 'Kartoharjo';
                const preset = districtPresets[dist] || districtPresets['Kartoharjo'] || [[-7.6298, 111.5239]];
                routeWaypoints = JSON.parse(JSON.stringify(preset));
                redrawWaypoints();
                focusMapToRoute();
            });
        }

        // Tombol Fokus
        const btnFocus = document.getElementById('btnFocusRoute');
        if (btnFocus) {
            btnFocus.addEventListener('click', focusMapToRoute);
        }

        let mapInitialized = false;

        function start() {
            if (mapInitialized && editMap) return;
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
            if (editMap) editMap.resize();
        });
    })();
    </script>
</x-layouts::app>
