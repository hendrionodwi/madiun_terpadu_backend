<x-layouts::app :title="__('Katalog Produk Daur Ulang TPS3R')">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-2 md:p-4" x-data="{ editModalOpen: false, activeProduct: {}, newPhotoPreview: null, editPhotoPreview: null }">
        <!-- Header Page -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                        Marketplace Sirkular
                    </span>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Ekosistem Nilai Tambah Sampah</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Katalog Produk Daur Ulang
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Kelola etalase produk olahan kompos, daur ulang plastik, maggot, dan kerajinan untuk dijual ke masyarakat.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('dashboard.tps3r.orders') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Pesanan Masuk
                </a>
                <a href="{{ route('dashboard.tps3r.withdrawals.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-800">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Pencairan Toko
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Inventaris Toko -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Macam Produk</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 font-bold">📦</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-amber-700 dark:text-amber-400">
                        {{ $totalProduk }} <span class="text-sm font-semibold text-zinc-500">SKU</span>
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500">Tercatat di katalog TPS3R</p>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Stok Fisik</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300 font-bold">📊</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-teal-700 dark:text-teal-400">
                        {{ number_format($totalStok, 0, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">unit</span>
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500">Siap dibeli oleh warga</p>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Produk Aktif Tampil</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 font-bold">✅</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-400">
                        {{ $produkAktif }} <span class="text-sm font-semibold text-zinc-500">aktif</span>
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500">Dapat di-checkout di mobile/web</p>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Nilai Inventaris</span>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 font-bold">💰</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-black text-blue-700 dark:text-blue-400">
                        Rp {{ number_format($estimasiNilaiInventaris, 0, ',', '.') }}
                    </h3>
                    <p class="mt-1 text-xs text-zinc-500">Estimasi omset total stok</p>
                </div>
            </div>
        </div>

        <!-- Form Tambah Produk Baru -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Tambah Produk Daur Ulang Baru
                    </h2>
                    <p class="text-xs text-zinc-500">Isi formulir untuk memasarkan produk hasil olahan sampah ke etalase publik.</p>
                </div>
                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    + Formulir Baru
                </span>
            </div>

            <form method="POST" action="{{ route('dashboard.tps3r.store-product') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Nama Produk
                    <input type="text" name="nama_produk" value="{{ old('nama_produk') }}" placeholder="Contoh: Pupuk Kompos Madiun 5kg" required class="rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:border-amber-600 focus:ring-amber-600">
                    @error('nama_produk')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Kategori Daur Ulang
                    <select name="kategori" required class="rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:border-amber-600 focus:ring-amber-600">
                        <option value="">Pilih kategori</option>
                        <option value="pupuk_kompos" @selected(old('kategori') === 'pupuk_kompos')>Pupuk Kompos Organik</option>
                        <option value="kerajinan_plastik" @selected(old('kategori') === 'kerajinan_plastik')>Kerajinan Plastik Daur Ulang</option>
                        <option value="maggot_pakan" @selected(old('kategori') === 'maggot_pakan')>Maggot BSF / Pakan Ternak</option>
                        <option value="ecobrick" @selected(old('kategori') === 'ecobrick')>Ecobrick / Balok Bangunan</option>
                        <option value="lainnya" @selected(old('kategori') === 'lainnya')>Produk Kreatif Lainnya</option>
                    </select>
                    @error('kategori')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Harga Jual Satuan (Rp)
                    <input type="number" name="harga" value="{{ old('harga') }}" min="0" step="500" placeholder="15000" required class="rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:border-amber-600 focus:ring-amber-600">
                    @error('harga')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Stok Awal (Unit/Bungkus)
                    <input type="number" name="stok" value="{{ old('stok', 10) }}" min="0" required class="rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:border-amber-600 focus:ring-amber-600">
                    @error('stok')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 xl:col-span-2">
                    Deskripsi Produk & Manfaat
                    <input type="text" name="deskripsi" value="{{ old('deskripsi') }}" placeholder="Contoh: Kompos murni terfermentasi dari sampah organik dedaunan pasar." class="rounded-xl border border-zinc-300 px-3.5 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:border-amber-600 focus:ring-amber-600">
                    @error('deskripsi')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <!-- Field Unggah Foto Produk dengan Live Preview -->
                <div class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 md:col-span-2 xl:col-span-3">
                    <span>Foto Produk (Opsional)</span>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <template x-if="newPhotoPreview">
                            <div class="relative size-16 shrink-0 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shadow-xs bg-zinc-100 dark:bg-zinc-800">
                                <img :src="newPhotoPreview" class="size-full object-cover">
                                <button type="button" @click="newPhotoPreview = null; $refs.newPhotoInput.value = ''" class="absolute top-1 right-1 rounded-full bg-rose-600/90 hover:bg-rose-700 text-white size-4 flex items-center justify-center text-[10px]" title="Hapus foto">✕</button>
                            </div>
                        </template>

                        <label class="flex flex-1 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 bg-zinc-50/60 p-3 text-center transition hover:border-amber-500 hover:bg-amber-50/50 dark:border-zinc-700 dark:bg-zinc-800/60 dark:hover:border-amber-500">
                            <svg class="size-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-xs text-zinc-600 dark:text-zinc-300 font-medium" x-text="newPhotoPreview ? 'Ganti foto produk terpilih...' : 'Pilih Foto Produk (Format: JPG, JPEG, PNG, WEBP — Maks. 2MB)'"></span>
                            <input type="file" name="foto" x-ref="newPhotoInput" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { newPhotoPreview = e.target.result; }; reader.readAsDataURL(file); } else { newPhotoPreview = null; }">
                        </label>
                    </div>
                    @error('foto')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </div>

                <div class="flex items-center gap-3 md:col-span-2 xl:col-span-2 pt-2">
                    <input type="hidden" name="is_available" value="0">
                    <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        <input type="checkbox" name="is_available" value="1" checked class="size-4 rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                        <span>Publikasikan langsung ke Katalog Marketplace (Tersedia untuk dipesan)</span>
                    </label>
                </div>

                <div class="flex items-end md:col-span-2 xl:col-span-1">
                    <button type="submit" class="w-full rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-700">
                        + Tambah ke Katalog
                    </button>
                </div>
            </form>
        </section>

        <!-- Tabel Daftar Produk Daur Ulang -->
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Daftar Produk di Etalase Toko
                    </h2>
                    <p class="text-xs text-zinc-500">Kelola informasi harga, stok fisik, atau ubah ketersediaan produk.</p>
                </div>
                <span class="text-xs font-semibold text-zinc-500">
                    Menampilkan {{ $products->total() }} produk
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                            <th class="px-4 py-3 text-right">Stok</th>
                            <th class="px-4 py-3 text-center">Status Katalog</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($products as $product)
                            <tr class="text-zinc-700 dark:text-zinc-200">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-11 shrink-0 rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center">
                                            @if ($product->foto)
                                                <img src="{{ $product->foto_url }}" alt="{{ $product->nama_produk }}" class="size-full object-cover">
                                            @else
                                                <span class="text-lg">
                                                    @switch($product->kategori)
                                                        @case('pupuk_kompos') 🌱 @break
                                                        @case('kerajinan_plastik') 🧺 @break
                                                        @case('maggot_pakan') 🐛 @break
                                                        @case('ecobrick') 🧱 @break
                                                        @default 📦
                                                    @endswitch
                                                </span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $product->nama_produk }}</p>
                                            @if ($product->deskripsi)
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1 max-w-xs">{{ $product->deskripsi }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ str_replace('_', ' ', $product->kategori) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-teal-700 dark:text-teal-400">
                                    Rp {{ number_format($product->harga, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    @if ($product->stok <= 0)
                                        <span class="text-rose-600 font-bold" title="Stok habis!">0 unit ⚠️</span>
                                    @elseif ($product->stok <= 5)
                                        <span class="text-amber-600 font-bold" title="Stok menipis!">{{ $product->stok }} unit ⚠️</span>
                                    @else
                                        <span>{{ $product->stok }} unit</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($product->stok <= 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300" title="Stok habis, produk otomatis disembunyikan dari etalase pembeli">
                                            <span class="size-1.5 rounded-full bg-rose-500"></span> Habis (Stok 0)
                                        </span>
                                    @elseif ($product->is_available)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            Disembunyikan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <!-- Tombol Trigger Edit Modal -->
                                        <button type="button"
                                            @click="activeProduct = {
                                                id: {{ $product->id }},
                                                nama_produk: '{{ addslashes($product->nama_produk) }}',
                                                kategori: '{{ $product->kategori }}',
                                                harga: {{ $product->harga }},
                                                stok: {{ $product->stok }},
                                                deskripsi: '{{ addslashes($product->deskripsi ?? '') }}',
                                                foto_url: '{{ $product->foto_url ?? '' }}',
                                                is_available: {{ $product->is_available ? 'true' : 'false' }},
                                                update_url: '{{ route('dashboard.tps3r.products.update', $product) }}'
                                            }; editPhotoPreview = null; if ($refs.editPhotoInput) { $refs.editPhotoInput.value = ''; } editModalOpen = true"
                                            class="inline-flex items-center gap-1 rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                            ✏️ Edit
                                        </button>

                                        <!-- Form Hapus Produk -->
                                        <form method="POST" action="{{ route('dashboard.tps3r.products.destroy', $product) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ addslashes($product->nama_produk) }} dari katalog?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300 dark:hover:bg-rose-950/60">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                    Belum ada produk daur ulang yang ditambahkan ke katalog. Gunakan formulir di atas untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </section>

        <!-- Modal Edit Produk (Alpine.js) -->
        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-sm" x-cloak>
            <div @click.away="editModalOpen = false" class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Edit Produk Daur Ulang
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        ✕
                    </button>
                </div>

                <form :action="activeProduct.update_url" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4">
                    @csrf
                    @method('PATCH')

                    <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Nama Produk
                        <input type="text" name="nama_produk" x-model="activeProduct.nama_produk" required class="rounded-xl border border-zinc-300 px-3.5 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    </label>

                    <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Kategori
                        <select name="kategori" x-model="activeProduct.kategori" required class="rounded-xl border border-zinc-300 px-3.5 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="pupuk_kompos">Pupuk Kompos Organik</option>
                            <option value="kerajinan_plastik">Kerajinan Plastik Daur Ulang</option>
                            <option value="maggot_pakan">Maggot BSF / Pakan Ternak</option>
                            <option value="ecobrick">Ecobrick / Balok Bangunan</option>
                            <option value="lainnya">Produk Kreatif Lainnya</option>
                        </select>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            Harga Satuan (Rp)
                            <input type="number" name="harga" x-model="activeProduct.harga" min="0" step="500" required class="rounded-xl border border-zinc-300 px-3.5 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </label>

                        <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            Stok (Unit)
                            <input type="number" name="stok" x-model="activeProduct.stok" min="0" required class="rounded-xl border border-zinc-300 px-3.5 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </label>
                    </div>

                    <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Deskripsi
                        <textarea name="deskripsi" x-model="activeProduct.deskripsi" rows="3" class="rounded-xl border border-zinc-300 px-3.5 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    </label>

                    <!-- Foto Produk saat edit -->
                    <div class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        <span>Foto Produk</span>
                        <div class="flex items-center gap-3">
                            <template x-if="editPhotoPreview">
                                <div class="relative size-14 shrink-0 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shadow-xs bg-zinc-100 dark:bg-zinc-800">
                                    <img :src="editPhotoPreview" class="size-full object-cover">
                                    <button type="button" @click="editPhotoPreview = null; $refs.editPhotoInput.value = ''" class="absolute top-0.5 right-0.5 rounded-full bg-rose-600 text-white size-4 flex items-center justify-center text-[10px]" title="Batalkan foto baru">✕</button>
                                </div>
                            </template>
                            <template x-if="!editPhotoPreview && activeProduct.foto_url">
                                <div class="size-14 shrink-0 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shadow-xs bg-zinc-100 dark:bg-zinc-800">
                                    <img :src="activeProduct.foto_url" class="size-full object-cover">
                                </div>
                            </template>

                            <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 bg-zinc-50/60 p-2.5 text-center transition hover:border-amber-500 hover:bg-amber-50/50 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <svg class="size-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs text-zinc-600 dark:text-zinc-300 font-medium" x-text="editPhotoPreview ? 'Foto baru dipilih (Klik untuk ganti)' : (activeProduct.foto_url ? 'Ganti foto produk...' : 'Unggah foto produk...')"></span>
                                <input type="file" name="foto" x-ref="editPhotoInput" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { editPhotoPreview = e.target.result; }; reader.readAsDataURL(file); } else { editPhotoPreview = null; }">
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="is_available" value="0">
                    <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        <input type="checkbox" name="is_available" value="1" :checked="activeProduct.is_available" class="size-4 rounded border-zinc-300 text-amber-600 focus:ring-amber-500">
                        <span>Tampilkan di Katalog Marketplace</span>
                    </label>

                    <div class="mt-2 flex justify-end gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        <button type="button" @click="editModalOpen = false" class="rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            Batal
                        </button>
                        <button type="submit" class="rounded-xl bg-amber-600 px-5 py-2 text-xs font-bold text-white hover:bg-amber-700">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
