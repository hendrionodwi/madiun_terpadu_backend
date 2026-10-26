<x-layouts::app :title="__('Dashboard Fasilitas TPS3R')">
    <div class="civic-page">
        <x-civic.page-hero
            eyebrow="Dashboard Fasilitas TPS3R"
            :title="$tps3rProfile->nama_tps3r ?? 'Fasilitas TPS3R Kota Madiun'"
            description="Kelola arus sampah masuk, kapasitas pengolahan, produk daur ulang, dan pesanan marketplace dari satu ruang kerja."
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.tps3r.deposits') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-civic-900 shadow-sm hover:bg-civic-50">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Catat setoran
                </a>
                <a href="{{ route('dashboard.tps3r.profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur-sm hover:bg-white/20">
                    Profil fasilitas
                </a>
            </x-slot:actions>

            <x-slot:meta>
                <div class="grid gap-3 text-xs text-civic-100/90 sm:grid-cols-3">
                    <div><span class="block text-[0.6rem] font-bold uppercase tracking-wider text-civic-300">Kode fasilitas</span><strong class="mt-0.5 block font-mono text-white">{{ $tps3rProfile->kode_tps3r ?? 'TPS3R-MADIUN' }}</strong></div>
                    <div><span class="block text-[0.6rem] font-bold uppercase tracking-wider text-civic-300">Wilayah</span><strong class="mt-0.5 block text-white">{{ $tps3rProfile->kecamatan ?? 'Kota Madiun' }}</strong></div>
                    <div><span class="block text-[0.6rem] font-bold uppercase tracking-wider text-civic-300">Kapasitas harian</span><strong class="mt-0.5 block text-white">{{ number_format($volume['kapasitas_harian_kg'], 0, ',', '.') }} kg</strong></div>
                </div>
            </x-slot:meta>
        </x-civic.page-hero>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <section aria-labelledby="ringkasan-fasilitas-title">
            <div class="mb-3">
                <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.14em] text-civic-700 dark:text-civic-300">Ringkasan fasilitas</p>
                <h2 id="ringkasan-fasilitas-title" class="mt-1 font-display text-lg font-extrabold text-slate-950 dark:text-white">Operasional hari ini</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-civic.stat-card label="Volume masuk hari ini" :value="number_format($volume['hari_ini_kg'], 1, ',', '.')" suffix="kg" description="Setoran yang diterima hari ini">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.38a1.13 1.13 0 0 1-1.13-1.13V14.25m17.25 4.5a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.13c.62 0 1.12-.5 1.12-1.13V14.25m-17.5 0V6.38c0-.62.5-1.13 1.13-1.13h9.37v9m-10.5 0h10.5m0-9h3.6c.3 0 .58.12.8.33l3.29 3.3c.21.2.33.49.33.79v4.58m-7.5 0h7.5" /></svg></x-slot:icon>
                </x-civic.stat-card>

                <x-civic.stat-card label="Utilisasi kapasitas" :value="$volume['persen_utilisasi'].'%'" description="Penggunaan kapasitas pengolahan" tone="sky" :href="route('dashboard.tps3r.volume-recap')">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.13c0-.41.34-.75.75-.75h3c.41 0 .75.34.75.75v7.12c0 .41-.34.75-.75.75h-3a.75.75 0 0 1-.75-.75v-7.12Zm6.75-4.5c0-.41.34-.75.75-.75h3c.41 0 .75.34.75.75v11.62c0 .41-.34.75-.75.75h-3a.75.75 0 0 1-.75-.75V8.63Zm6.75-4.5c0-.41.34-.75.75-.75h3c.41 0 .75.34.75.75v16.12c0 .41-.34.75-.75.75h-3a.75.75 0 0 1-.75-.75V4.13Z" /></svg></x-slot:icon>
                </x-civic.stat-card>

                <x-civic.stat-card label="Katalog daur ulang" :value="$marketplace['total_produk']" suffix="produk" :description="'Total stok siap jual: '.$marketplace['total_stok'].' unit'" tone="amber" :href="route('dashboard.tps3r.products')">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.39c.51 0 .96.34 1.1.83l.38 1.42m0 0L6.75 12h10.88l1.87-6.75H5.12ZM6.75 12l-1.5 1.5h13.5m-11.63 4.88a.38.38 0 1 1-.75 0 .38.38 0 0 1 .75 0Zm10.5 0a.38.38 0 1 1-.75 0 .38.38 0 0 1 .75 0Z" /></svg></x-slot:icon>
                </x-civic.stat-card>

                <x-civic.stat-card label="Total transaksi" :value="'Rp '.number_format($marketplace['total_pendapatan'], 0, ',', '.')" description="Pendapatan marketplace sirkular" tone="violet" :href="route('dashboard.tps3r.withdrawals.index')">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M3.75 5.25h16.5A1.5 1.5 0 0 1 21.75 6.75v10.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5ZM6.75 15h2.25" /></svg></x-slot:icon>
                </x-civic.stat-card>
            </div>
        </section>

        <x-civic.section-card title="Pusat kerja TPS3R" description="Akses seluruh siklus sampah dan marketplace tanpa menumpuk pekerjaan pada satu dashboard.">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <x-civic.module-link :href="route('dashboard.tps3r.deposits')" title="Setoran Sampah" description="Terima manifest petugas, periksa berat, dan verifikasi komposisi sampah." meta="Penerimaan fasilitas">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v1.5M3 21v-6m0 0 3 3m-3-3-3 3m6.75-13.5H19.5A1.5 1.5 0 0 1 21 6v12a1.5 1.5 0 0 1-1.5 1.5H8.25A1.5 1.5 0 0 1 6.75 18V6A1.5 1.5 0 0 1 8.25 4.5Z" /></svg></x-slot:icon>
                </x-civic.module-link>
                <x-civic.module-link :href="route('dashboard.tps3r.volume-recap')" title="Rekap Volume Sampah" description="Analisis tonase masuk, komposisi kategori, dan beban fasilitas." meta="Analitik kapasitas" tone="sky">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.13c0-.41.34-.75.75-.75h3c.41 0 .75.34.75.75v7.12c0 .41-.34.75-.75.75h-3a.75.75 0 0 1-.75-.75v-7.12Zm6.75-4.5c0-.41.34-.75.75-.75h3c.41 0 .75.34.75.75v11.62c0 .41-.34.75-.75.75h-3a.75.75 0 0 1-.75-.75V8.63Zm6.75-4.5c0-.41.34-.75.75-.75h3c.41 0 .75.34.75.75v16.12c0 .41-.34.75-.75.75h-3a.75.75 0 0 1-.75-.75V4.13Z" /></svg></x-slot:icon>
                </x-civic.module-link>
                <x-civic.module-link :href="route('dashboard.tps3r.processing.index')" title="Pengolahan Sampah" description="Kelola batch kompos, pencacahan plastik, dan hasil pengolahan." meta="Proses & konversi" tone="violet">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12a7.5 7.5 0 0 1 12.75-5.36L19.5 9m0-4.5V9H15m4.5 3a7.5 7.5 0 0 1-12.75 5.36L4.5 15m0 4.5V15H9" /></svg></x-slot:icon>
                </x-civic.module-link>
                <x-civic.module-link :href="route('dashboard.tps3r.products')" title="Marketplace Daur Ulang" description="Kelola katalog, harga, stok, dan ketersediaan produk olahan." meta="Produk sirkular" tone="amber">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.39c.51 0 .96.34 1.1.83l.38 1.42L6.75 12h10.88l1.87-6.75H5.12M6.75 12l-1.5 1.5h13.5" /></svg></x-slot:icon>
                </x-civic.module-link>
                <x-civic.module-link :href="route('dashboard.tps3r.orders')" title="Pesanan Marketplace" description="Proses pesanan masuk, pengemasan, pengiriman, dan serah terima." meta="Order fulfillment" tone="sky">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8.25-9-5.25-9 5.25m18 0-9 5.25m9-5.25v7.5L12 21m0-7.5L3 8.25m9 5.25V21M3 8.25v7.5L12 21" /></svg></x-slot:icon>
                </x-civic.module-link>
                <x-civic.module-link :href="route('dashboard.tps3r.withdrawals.index')" title="Pendapatan Toko" description="Pantau saldo tersedia dan ajukan pencairan pendapatan produk." meta="Keuangan fasilitas">
                    <x-slot:icon><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0 1 15.8 2.1c.98.27 1.95-.47 1.95-1.49V4.64c0-1.02-.97-1.76-1.95-1.49a60.1 60.1 0 0 1-15.8 2.1v13.5ZM8.25 6.75v10.5" /></svg></x-slot:icon>
                </x-civic.module-link>
            </div>
        </x-civic.section-card>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-civic.section-card title="Setoran terbaru" description="Manifest sampah yang baru masuk ke fasilitas.">
                <x-slot:action><a href="{{ route('dashboard.tps3r.deposits') }}" class="civic-link">Lihat semua <span aria-hidden="true">&rarr;</span></a></x-slot:action>
                <div class="space-y-1">
                    @forelse ($volume['setoran_terbaru'] as $setoran)
                        <div class="flex items-center justify-between gap-4 rounded-xl px-3 py-3 hover:bg-civic-50 dark:hover:bg-civic-950/30">
                            <div class="min-w-0">
                                <p class="font-mono text-xs font-bold text-civic-700 dark:text-civic-300">{{ $setoran->nomor_resi }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500"><span class="capitalize">{{ str_replace('_', ' ', $setoran->jenis_sampah) }}</span> &middot; {{ $setoran->sumber_sampah }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="font-display text-sm font-extrabold text-slate-950 dark:text-white">{{ number_format($setoran->berat_kg, 1, ',', '.') }} kg</p>
                                <p class="mt-1 text-[0.68rem] text-slate-500">{{ \Illuminate\Support\Carbon::parse($setoran->tanggal_setor)->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="civic-empty">Belum ada catatan setoran sampah.</div>
                    @endforelse
                </div>
            </x-civic.section-card>

            <x-civic.section-card title="Katalog produk olahan" description="Produk yang aktif pada marketplace daur ulang.">
                <x-slot:action><a href="{{ route('dashboard.tps3r.products') }}" class="civic-link">Kelola produk <span aria-hidden="true">&rarr;</span></a></x-slot:action>
                <div class="space-y-1">
                    @forelse ($marketplace['katalog']->take(4) as $product)
                        <div class="flex items-center justify-between gap-4 rounded-xl px-3 py-3 hover:bg-civic-50 dark:hover:bg-civic-950/30">
                            <div class="min-w-0">
                                <h3 class="truncate font-display text-sm font-bold text-slate-950 dark:text-white">{{ $product->nama_produk }}</h3>
                                <p class="mt-1 truncate text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $product->kategori) }} &middot; Stok {{ $product->stok }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="font-display text-sm font-extrabold text-civic-700 dark:text-civic-300">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                                <span class="mt-1 {{ $product->is_available ? 'civic-status-success' : 'civic-status-danger' }}">{{ $product->is_available ? 'Tersedia' : 'Habis' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="civic-empty">Belum ada produk daur ulang yang terdaftar.</div>
                    @endforelse
                </div>
            </x-civic.section-card>
        </div>
    </div>
</x-layouts::app>
