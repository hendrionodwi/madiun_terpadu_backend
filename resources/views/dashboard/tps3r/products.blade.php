<x-layouts::app :title="__('Katalog Produk Daur Ulang TPS3R')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div
        class="civic-page mx-auto w-full max-w-7xl"
        x-data="{ editModalOpen: false, activeProduct: {}, newPhotoPreview: null, editPhotoPreview: null }"
    >
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Produk Daur Ulang"
            eyebrow="Marketplace Sirkular · Ekosistem Nilai Tambah Sampah"
            title="Katalog Produk Daur Ulang"
            description="Kelola etalase produk olahan kompos, daur ulang plastik, maggot, dan kerajinan untuk dijual ke masyarakat."
            icon="cube"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.tps3r.orders') }}" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                    <flux:icon.shopping-bag class="size-4" />
                    Pesanan Masuk
                </a>
                <a href="{{ route('dashboard.tps3r.withdrawals.index') }}" class="civic-button-primary !min-h-10 !py-2 !text-xs">
                    <flux:icon.banknotes class="size-4" />
                    Pencairan Toko
                </a>
            </x-slot:actions>
        </x-civic.subpage-header>

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Inventaris Toko -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="civic-stat-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Macam Produk</span>
                    <span class="civic-icon-tile !size-8"><flux:icon.cube class="size-4" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="font-display text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                        {{ $totalProduk }} <span class="text-sm font-semibold text-slate-500">SKU</span>
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tercatat di katalog TPS3R</p>
                </div>
            </div>

            <div class="civic-stat-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Stok Fisik</span>
                    <span class="civic-icon-tile !size-8"><flux:icon.chart-bar class="size-4" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="font-display text-2xl font-extrabold text-civic-700 dark:text-civic-300">
                        {{ number_format($totalStok, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-500">unit</span>
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Siap dibeli oleh warga</p>
                </div>
            </div>

            <div class="civic-stat-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Produk Aktif Tampil</span>
                    <span class="civic-icon-tile !size-8"><flux:icon.check-circle class="size-4" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="font-display text-2xl font-extrabold text-emerald-700 dark:text-emerald-400">
                        {{ $produkAktif }} <span class="text-sm font-semibold text-slate-500">aktif</span>
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Dapat di-checkout di mobile/web</p>
                </div>
            </div>

            <div class="civic-stat-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nilai Inventaris</span>
                    <span class="civic-icon-tile !size-8"><flux:icon.banknotes class="size-4" /></span>
                </div>
                <div class="mt-3">
                    <h3 class="font-display text-2xl font-extrabold text-sky-700 dark:text-sky-400">
                        Rp {{ number_format($estimasiNilaiInventaris, 0, ',', '.') }}
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Estimasi omset total stok</p>
                </div>
            </div>
        </div>

        <!-- Form Tambah Produk Baru -->
        <section class="civic-card p-5 sm:p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <div>
                    <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                        Tambah Produk Daur Ulang Baru
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Isi formulir untuk memasarkan produk hasil olahan sampah ke etalase publik.</p>
                </div>
                <span class="civic-status-success">
                    + Formulir Baru
                </span>
            </div>

            <form method="POST" action="{{ route('dashboard.tps3r.store-product') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Nama Produk</span>
                    <input type="text" name="nama_produk" value="{{ old('nama_produk') }}" placeholder="Contoh: Pupuk Kompos Madiun 5kg" required class="{{ $fieldClass }}">
                    @error('nama_produk')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Kategori Daur Ulang</span>
                    <select name="kategori" required class="{{ $fieldClass }}">
                        <option value="">Pilih kategori</option>
                        <option value="pupuk_kompos" @selected(old('kategori') === 'pupuk_kompos')>Pupuk Kompos Organik</option>
                        <option value="kerajinan_plastik" @selected(old('kategori') === 'kerajinan_plastik')>Kerajinan Plastik Daur Ulang</option>
                        <option value="maggot_pakan" @selected(old('kategori') === 'maggot_pakan')>Maggot BSF / Pakan Ternak</option>
                        <option value="ecobrick" @selected(old('kategori') === 'ecobrick')>Ecobrick / Balok Bangunan</option>
                        <option value="lainnya" @selected(old('kategori') === 'lainnya')>Produk Kreatif Lainnya</option>
                    </select>
                    @error('kategori')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Harga Jual Satuan (Rp)</span>
                    <input type="number" name="harga" value="{{ old('harga') }}" min="0" step="500" placeholder="15000" required class="{{ $fieldClass }}">
                    @error('harga')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <span>Stok Awal (Unit/Bungkus)</span>
                    <input type="number" name="stok" value="{{ old('stok', 10) }}" min="0" required class="{{ $fieldClass }}">
                    @error('stok')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 xl:col-span-2">
                    <span>Deskripsi Produk & Manfaat</span>
                    <input type="text" name="deskripsi" value="{{ old('deskripsi') }}" placeholder="Contoh: Kompos murni terfermentasi dari sampah organik dedaunan pasar." class="{{ $fieldClass }}">
                    @error('deskripsi')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <!-- Field Unggah Foto Produk dengan Live Preview -->
                <div class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 md:col-span-2 xl:col-span-3">
                    <span>Foto Produk (Opsional)</span>
                    <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                        <template x-if="newPhotoPreview">
                            <div class="relative size-16 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-xs dark:border-slate-700 dark:bg-slate-800">
                                <img :src="newPhotoPreview" class="size-full object-cover">
                                <button type="button" @click="newPhotoPreview = null; $refs.newPhotoInput.value = ''" class="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-red-600/90 text-xs text-white hover:bg-red-700" title="Hapus foto">✕</button>
                            </div>
                        </template>

                        <label class="flex w-full flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-3 text-center transition hover:border-civic-500 hover:bg-civic-50/50 dark:border-slate-700 dark:bg-slate-800/60 dark:hover:border-civic-500">
                            <flux:icon.photo class="size-5 shrink-0 text-civic-700 dark:text-civic-300" />
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-300" x-text="newPhotoPreview ? 'Ganti foto produk terpilih...' : 'Pilih Foto Produk (Format: JPG, JPEG, PNG, WEBP — Maks. 5MB)'"></span>
                            <input type="file" name="foto" x-ref="newPhotoInput" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { newPhotoPreview = e.target.result; }; reader.readAsDataURL(file); } else { newPhotoPreview = null; }">
                        </label>
                    </div>
                    @error('foto')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </div>

                <div class="grid gap-3 pt-2 sm:grid-cols-2 md:col-span-2 xl:col-span-2">
                    <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-300">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1" checked class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600">
                        <span>Tampilkan di katalog</span>
                    </label>
                    <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-300">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600">
                        <span>Jadikan produk unggulan</span>
                    </label>
                </div>

                <div class="flex items-end md:col-span-2 xl:col-span-1">
                    <button type="submit" class="civic-button-primary w-full">
                        + Tambah ke Katalog
                    </button>
                </div>
            </form>
        </section>

        <!-- Tabel Daftar Produk Daur Ulang -->
        <section data-ajax-zone="tps3r-products" class="civic-card overflow-hidden">
            <div class="civic-section-header">
                <div>
                    <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                        Daftar Produk di Etalase Toko
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Kelola informasi harga, stok fisik, atau ubah ketersediaan produk.</p>
                </div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    Menampilkan {{ $products->total() }} produk
                </span>
            </div>

            <div class="civic-table-wrap rounded-none border-0">
                <table class="civic-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Stok</th>
                            <th class="text-center">Status Katalog</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                                            @if ($product->foto)
                                                <img src="{{ $product->foto_url }}" alt="{{ $product->nama_produk }}" class="size-full object-cover">
                                            @else
                                                <flux:icon.cube class="size-5 text-slate-400" />
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="truncate font-bold text-slate-950 dark:text-white">{{ $product->nama_produk }}</p>
                                                @if ($product->is_featured)
                                                    <span class="civic-status-warning">Unggulan</span>
                                                @endif
                                            </div>
                                            @if ($product->deskripsi)
                                                <p class="line-clamp-1 max-w-xs text-xs text-slate-500 dark:text-slate-400">{{ $product->deskripsi }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ str_replace('_', ' ', $product->kategori) }}
                                    </span>
                                </td>
                                <td class="text-right font-bold text-civic-700 dark:text-civic-300">
                                    Rp {{ number_format($product->harga, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-medium">
                                    @if ($product->stok <= 0)
                                        <span class="inline-flex items-center gap-1 font-bold text-red-600" title="Stok habis"><flux:icon.exclamation-triangle class="size-3.5" /> 0 unit</span>
                                    @elseif ($product->stok <= 5)
                                        <span class="inline-flex items-center gap-1 font-bold text-amber-600" title="Stok menipis"><flux:icon.exclamation-triangle class="size-3.5" /> {{ $product->stok }} unit</span>
                                    @else
                                        <span>{{ $product->stok }} unit</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($product->stok <= 0)
                                        <span class="civic-status-danger" title="Stok habis, produk otomatis disembunyikan dari etalase pembeli">
                                            Habis (Stok 0)
                                        </span>
                                    @elseif ($product->is_available)
                                        <span class="civic-status-success">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="civic-status bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                            Disembunyikan
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="activeProduct = {
                                                id: {{ $product->id }},
                                                nama_produk: '{{ addslashes($product->nama_produk) }}',
                                                kategori: '{{ $product->kategori }}',
                                                harga: {{ $product->harga }},
                                                stok: {{ $product->stok }},
                                                deskripsi: '{{ addslashes($product->deskripsi ?? '') }}',
                                                foto_url: '{{ $product->foto_url ?? '' }}',
                                                is_available: {{ $product->is_available ? 'true' : 'false' }},
                                                is_featured: {{ $product->is_featured ? 'true' : 'false' }},
                                                update_url: '{{ route('dashboard.tps3r.products.update', $product) }}'
                                            }; editPhotoPreview = null; if ($refs.editPhotoInput) { $refs.editPhotoInput.value = ''; } editModalOpen = true"
                                            class="civic-button-secondary !min-h-8 !px-2.5 !py-1 !text-xs"
                                        >
                                            <flux:icon.pencil-square class="size-3.5" />
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('dashboard.tps3r.products.destroy', $product) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ addslashes($product->nama_produk) }} dari katalog?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-8 items-center gap-1 rounded-xl border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 transition hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-950/60">
                                                <flux:icon.trash class="size-3.5" />
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada produk daur ulang yang ditambahkan ke katalog. Gunakan formulir di atas untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                    {{ $products->links() }}
                </div>
            @endif
        </section>

        <!-- Modal Edit Produk (Alpine.js) -->
        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" x-cloak>
            <div @click.away="editModalOpen = false" class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="font-display text-base font-extrabold text-slate-950 dark:text-white">
                        Edit Produk Daur Ulang
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        ✕
                    </button>
                </div>

                <form :action="activeProduct.update_url" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4">
                    @csrf
                    @method('PATCH')

                    <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                        <span>Nama Produk</span>
                        <input type="text" name="nama_produk" x-model="activeProduct.nama_produk" required class="{{ $fieldClass }}">
                    </label>

                    <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                        <span>Kategori</span>
                        <select name="kategori" x-model="activeProduct.kategori" required class="{{ $fieldClass }}">
                            <option value="pupuk_kompos">Pupuk Kompos Organik</option>
                            <option value="kerajinan_plastik">Kerajinan Plastik Daur Ulang</option>
                            <option value="maggot_pakan">Maggot BSF / Pakan Ternak</option>
                            <option value="ecobrick">Ecobrick / Balok Bangunan</option>
                            <option value="lainnya">Produk Kreatif Lainnya</option>
                        </select>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span>Harga Satuan (Rp)</span>
                            <input type="number" name="harga" x-model="activeProduct.harga" min="0" step="500" required class="{{ $fieldClass }}">
                        </label>

                        <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span>Stok (Unit)</span>
                            <input type="number" name="stok" x-model="activeProduct.stok" min="0" required class="{{ $fieldClass }}">
                        </label>
                    </div>

                    <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                        <span>Deskripsi</span>
                        <textarea name="deskripsi" x-model="activeProduct.deskripsi" rows="3" class="{{ $fieldClass }}"></textarea>
                    </label>

                    <!-- Foto Produk saat edit -->
                    <div class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                        <span>Foto Produk</span>
                        <div class="flex items-center gap-3">
                            <template x-if="editPhotoPreview">
                                <div class="relative size-14 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-xs dark:border-slate-700 dark:bg-slate-800">
                                    <img :src="editPhotoPreview" class="size-full object-cover">
                                    <button type="button" @click="editPhotoPreview = null; $refs.editPhotoInput.value = ''" class="absolute top-0.5 right-0.5 flex size-4 items-center justify-center rounded-full bg-red-600 text-xs text-white" title="Batalkan foto baru">✕</button>
                                </div>
                            </template>
                            <template x-if="!editPhotoPreview && activeProduct.foto_url">
                                <div class="size-14 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-xs dark:border-slate-700 dark:bg-slate-800">
                                    <img :src="activeProduct.foto_url" class="size-full object-cover">
                                </div>
                            </template>

                            <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-2.5 text-center transition hover:border-civic-500 hover:bg-civic-50/50 dark:border-slate-700 dark:bg-slate-800/60">
                                <flux:icon.photo class="size-4 shrink-0 text-civic-700 dark:text-civic-300" />
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300" x-text="editPhotoPreview ? 'Foto baru dipilih (Klik untuk ganti)' : (activeProduct.foto_url ? 'Ganti foto produk...' : 'Unggah foto produk...')"></span>
                                <input type="file" name="foto" x-ref="editPhotoInput" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { editPhotoPreview = e.target.result; }; reader.readAsDataURL(file); } else { editPhotoPreview = null; }">
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="is_available" value="0">
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="is_available" value="1" x-bind:checked="activeProduct.is_available" class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600">
                        <span>Tampilkan di Katalog Marketplace</span>
                    </label>

                    <input type="hidden" name="is_featured" value="0">
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="is_featured" value="1" x-bind:checked="activeProduct.is_featured" class="size-4 rounded border-slate-300 text-civic-700 focus:ring-civic-600">
                        <span>Jadikan produk unggulan</span>
                    </label>

                    <div class="mt-2 flex justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <button type="button" @click="editModalOpen = false" class="civic-button-secondary !min-h-9 !py-1.5 !text-xs">
                            Batal
                        </button>
                        <button type="submit" class="civic-button-primary !min-h-9 !py-1.5 !text-xs">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
