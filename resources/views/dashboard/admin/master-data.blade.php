<x-layouts::app :title="__('Kelola Data Master - Admin DLH')">
    <div x-data="{
        editModalOpen: false,
        activeItem: null,
        selectedGroup: 'waste_category',
        openEditItem(item) {
            this.activeItem = { ...item };
            this.editModalOpen = true;
        }
    }" class="flex flex-col gap-6 p-2 md:p-4">
        <!-- Breadcrumb / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1">
                    <a href="{{ route('dashboard.admin') }}" class="hover:text-emerald-600 transition">Dashboard Admin</a>
                    <span>/</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-semibold">Data Master</span>
                </div>
                <h1 class="text-2xl font-black text-zinc-900 dark:text-zinc-100">Konfigurasi & Data Master Sistem</h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Kelola kategori sampah, tarif konversi poin per kilogram, kategori pengaduan, dan parameter wilayah.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-700 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-900/30 dark:border-rose-700 p-4 text-sm text-rose-800 dark:text-rose-200 flex items-center gap-2">
                <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Master Data -->
            <div class="lg:col-span-1 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm h-fit text-xs">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-1">Tambah Data Master</h2>
                <p class="text-zinc-500 mb-4">Parameter konfigurasi operasional aplikasi.</p>

                <form method="POST" action="{{ route('dashboard.admin.master-data.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Grup Master *</label>
                        <select name="group" x-model="selectedGroup" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="waste_category">Kategori Sampah (waste_category)</option>
                            <option value="districts">Wilayah Kecamatan (districts)</option>
                            <option value="point_conversion">Konversi Nilai Poin (point_conversion)</option>
                            <option value="complaint_categories">Kategori Aduan (complaint_categories)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kode Unik *</label>
                            <input type="text" name="code" placeholder="PLASTIK_PET" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Urutan</label>
                            <input type="number" name="sort_order" value="1" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama / Label *</label>
                        <input type="text" name="name" placeholder="Plastik Botol PET Bersih" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>

                    <div x-show="selectedGroup === 'waste_category' || selectedGroup === 'waste_categories'">
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Pilar Induk (Khusus Kategori Sampah)</label>
                        <select name="pillar" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Otomatis dari Kode</option>
                            <option value="organik">Organik (Kompos / Pakan Maggot)</option>
                            <option value="anorganik_daur_ulang">Anorganik Daur Ulang (Plastik/Kertas/Logam/dll)</option>
                            <option value="residu">Residu (Wajib TPA Winongo)</option>
                        </select>
                    </div>

                    <div x-show="selectedGroup !== 'districts' && selectedGroup !== 'complaint_categories'" class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nilai Numerik</label>
                            <input type="number" step="0.01" name="numeric_value" placeholder="150" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Satuan Unit</label>
                            <input type="text" name="unit" placeholder="Poin / Kg" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Deskripsi Tambahan</label>
                        <textarea name="description" rows="2" placeholder="Penjelasan atau ketentuan jenis sampah..." class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                        Simpan Data Master
                    </button>
                </form>
            </div>

            <!-- Tabel Data Master -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Group Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                    <a href="{{ route('dashboard.admin.master-data') }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ ! $currentGroup ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                        Semua Grup
                    </a>
                    @foreach ($groups as $grpKey => $grpLabel)
                        <a href="{{ route('dashboard.admin.master-data', ['group' => $grpKey]) }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ $currentGroup === $grpKey ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                            {{ $grpLabel }}
                        </a>
                    @endforeach
                    <a href="{{ route('dashboard.admin.retributions') }}" class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 ml-auto border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-300 flex items-center gap-1.5" title="Konfigurasi tarif retribusi dikelola pada form khusus">
                        <span>⚙️ Kelola Retribusi</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm overflow-hidden text-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 font-semibold uppercase">
                                    <th class="py-3 px-4">Grup & Kode</th>
                                    <th class="py-3 px-4">Nama Item</th>
                                    <th class="py-3 px-4">Nilai & Satuan</th>
                                    <th class="py-3 px-4">Deskripsi</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse ($items as $item)
                                    <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                                        <td class="py-3.5 px-4">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                                {{ $groups[$item->group] ?? ucfirst(str_replace('_', ' ', $item->group)) }}
                                            </span>
                                            <p class="font-mono font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $item->code }}</p>
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                            <div>{{ $item->name }}</div>
                                            @if ($item->group === 'waste_category')
                                                @php
                                                    $pillar = $item->pillar ?? \App\Models\MasterData::resolvePillar($item->code);
                                                    $pillarBadge = match($pillar) {
                                                        'organik' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                                                        'residu' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
                                                        default => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                                                    };
                                                @endphp
                                                <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $pillarBadge }}">
                                                    Pilar: {{ ucfirst(str_replace('_', ' ', $pillar)) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if ($item->numeric_value !== null)
                                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ number_format($item->numeric_value, 0) }}</span>
                                                <span class="text-zinc-400 text-[11px]">{{ $item->unit }}</span>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-zinc-500 text-[11px] max-w-xs truncate">
                                            {{ $item->description ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-red-100 text-red-800' }}">
                                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" @click="openEditItem({{ json_encode($item) }})" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 transition">
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('dashboard.admin.master-data.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data master {{ $item->name }}?');">
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
                                        <td colspan="6" class="py-8 text-center text-zinc-400">
                                            Tidak ada data master yang sesuai filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($items->hasPages())
                        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal Edit Data Master -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @keydown.escape.window="editModalOpen = false">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="editModalOpen = false">
                <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/50">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <span>Edit Data Master</span>
                        <span class="font-mono text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300" x-text="activeItem ? activeItem.code : ''"></span>
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="activeItem ? ('{{ url('dashboard/admin/master-data') }}/' + activeItem.id) : ''" method="POST" class="p-5 overflow-y-auto space-y-3 text-xs">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Grup Master *</label>
                        <select name="group" x-model="activeItem ? activeItem.group : ''" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="waste_category">Kategori Sampah (waste_category)</option>
                            <option value="districts">Wilayah Kecamatan (districts)</option>
                            <option value="point_conversion">Konversi Nilai Poin (point_conversion)</option>
                            <option value="complaint_categories">Kategori Aduan (complaint_categories)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Kode Unik *</label>
                            <input type="text" name="code" :value="activeItem ? activeItem.code : ''" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Urutan</label>
                            <input type="number" name="sort_order" :value="activeItem ? activeItem.sort_order : 0" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama / Label *</label>
                        <input type="text" name="name" :value="activeItem ? activeItem.name : ''" required class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>

                    <div x-show="activeItem && (activeItem.group === 'waste_category' || activeItem.group === 'waste_categories')">
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Pilar Induk Sampah</label>
                        <select name="pillar" :value="activeItem ? (activeItem.pillar || '') : ''" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Otomatis dari Kode</option>
                            <option value="organik">Organik (Kompos / Pakan Maggot)</option>
                            <option value="anorganik_daur_ulang">Anorganik Daur Ulang (Plastik/Kertas/Logam/dll)</option>
                            <option value="residu">Residu (Wajib TPA Winongo)</option>
                        </select>
                    </div>

                    <div x-show="activeItem && activeItem.group !== 'districts' && activeItem.group !== 'complaint_categories'" class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nilai Numerik</label>
                            <input type="number" step="0.01" name="numeric_value" :value="activeItem ? activeItem.numeric_value : ''" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Satuan Unit</label>
                            <input type="text" name="unit" :value="activeItem ? activeItem.unit : ''" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Status Keaktifan</label>
                        <select name="is_active" :value="activeItem ? (activeItem.is_active ? '1' : '0') : '1'" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Deskripsi Tambahan</label>
                        <textarea name="description" rows="2" :value="activeItem ? activeItem.description : ''" class="w-full px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold hover:bg-zinc-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
