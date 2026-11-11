<x-layouts::app :title="__('Kelola Data Master - Admin DLH')">
    @php
        $fieldClass = 'min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-civic-500 focus:bg-white focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div
        x-data="{
            editModalOpen: false,
            activeItem: {
                id: null,
                group: '',
                code: '',
                sort_order: 0,
                name: '',
                pillar: '',
                numeric_value: null,
                unit: '',
                is_active: true,
                description: '',
            },
            selectedGroup: 'waste_category',
            openEditItem(item) {
                this.activeItem = { ...item };
                this.editModalOpen = true;
            }
        }"
        class="civic-page"
    >
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard Admin"
            breadcrumbRoute="dashboard.admin"
            current="Data Master"
            eyebrow="Referensi Operasional"
            title="Konfigurasi & Data Master Sistem"
            description="Kelola kategori sampah, tarif konversi poin per kilogram, kategori pengaduan, dan parameter wilayah."
            icon="cog-6-tooth"
        />

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger flex items-center gap-2" role="alert">
                <flux:icon.exclamation-circle class="size-5 shrink-0" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form Tambah Master Data -->
            <div class="civic-card h-fit p-5 text-xs lg:col-span-1">
                <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Tambah Data Master</h2>
                <p class="mt-0.5 mb-4 text-slate-500 dark:text-slate-400">Parameter konfigurasi operasional aplikasi.</p>

                <form method="POST" action="{{ route('dashboard.admin.master-data.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Grup Master *</label>
                        <select name="group" x-model="selectedGroup" required class="{{ $fieldClass }}">
                            <option value="waste_category">Kategori Sampah (waste_category)</option>
                            <option value="districts">Wilayah Kecamatan (districts)</option>
                            <option value="point_conversion">Konversi Nilai Poin (point_conversion)</option>
                            <option value="complaint_categories">Kategori Aduan (complaint_categories)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Kode Unik *</label>
                            <input type="text" name="code" placeholder="PLASTIK_PET" required class="{{ $fieldClass }} font-mono">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Urutan</label>
                            <input type="number" name="sort_order" value="1" class="{{ $fieldClass }}">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Nama / Label *</label>
                        <input type="text" name="name" placeholder="Plastik Botol PET Bersih" required class="{{ $fieldClass }}">
                    </div>

                    <div x-show="selectedGroup === 'waste_category' || selectedGroup === 'waste_categories'">
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Pilar Induk (Khusus Kategori Sampah)</label>
                        <select name="pillar" class="{{ $fieldClass }}">
                            <option value="">Otomatis dari Kode</option>
                            <option value="organik">Organik (Kompos / Pakan Maggot)</option>
                            <option value="anorganik_daur_ulang">Anorganik Daur Ulang (Plastik/Kertas/Logam/dll)</option>
                            <option value="residu">Residu (Wajib TPA Winongo)</option>
                        </select>
                    </div>

                    <div x-show="selectedGroup !== 'districts' && selectedGroup !== 'complaint_categories'" class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Nilai Numerik</label>
                            <input type="number" step="0.01" name="numeric_value" placeholder="150" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Satuan Unit</label>
                            <input type="text" name="unit" placeholder="Poin / Kg" class="{{ $fieldClass }}">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Deskripsi Tambahan</label>
                        <textarea name="description" rows="2" placeholder="Penjelasan atau ketentuan jenis sampah..." class="{{ $fieldClass }}"></textarea>
                    </div>

                    <button type="submit" class="civic-button-primary w-full !py-2.5 !text-xs">
                        Simpan Data Master
                    </button>
                </form>
            </div>

            <!-- Tabel Data Master -->
            <div data-ajax-zone="admin-master-data" class="space-y-4 lg:col-span-2">
                <!-- Group Filter Pills -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <div class="civic-filter-rail">
                        <a href="{{ route('dashboard.admin.master-data') }}" data-ajax-link class="civic-filter-chip !text-xs {{ ! $currentGroup ? 'civic-filter-chip-active' : '' }}">
                            Semua Grup
                        </a>
                        @foreach ($groups as $grpKey => $grpLabel)
                            <a href="{{ route('dashboard.admin.master-data', ['group' => $grpKey]) }}" data-ajax-link class="civic-filter-chip !text-xs {{ $currentGroup === $grpKey ? 'civic-filter-chip-active' : '' }}">
                                {{ $grpLabel }}
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('dashboard.admin.retributions') }}" class="ml-auto flex shrink-0 items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 font-semibold text-amber-800 transition hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-300" title="Konfigurasi tarif retribusi dikelola pada form khusus">
                        <span class="inline-flex items-center gap-1.5"><flux:icon.cog-6-tooth class="size-3.5" /> Kelola Retribusi</span>
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>

                <div class="civic-card overflow-hidden">
                    <div class="civic-table-wrap rounded-none border-0">
                        <table class="civic-table">
                            <thead>
                                <tr>
                                    <th>Grup & Kode</th>
                                    <th>Nama Item</th>
                                    <th>Nilai & Satuan</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr>
                                        <td>
                                            <span class="inline-block rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                {{ $groups[$item->group] ?? ucfirst(str_replace('_', ' ', $item->group)) }}
                                            </span>
                                            <p class="mt-1 font-mono font-bold text-civic-700 dark:text-civic-300">{{ $item->code }}</p>
                                        </td>
                                        <td class="font-bold text-slate-950 dark:text-white">
                                            <div>{{ $item->name }}</div>
                                            @if ($item->group === 'waste_category')
                                                @php
                                                    $pillar = $item->pillar ?? \App\Models\MasterData::resolvePillar($item->code);
                                                    $pillarBadge = match ($pillar) {
                                                        'organik' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                                                        'residu' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300',
                                                        default => 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300',
                                                    };
                                                @endphp
                                                <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $pillarBadge }}">
                                                    Pilar: {{ ucfirst(str_replace('_', ' ', $pillar)) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->numeric_value !== null)
                                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($item->numeric_value, 0) }}</span>
                                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $item->unit }}</span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="max-w-xs truncate text-xs text-slate-500 dark:text-slate-400">
                                            {{ $item->description ?? '-' }}
                                        </td>
                                        <td>
                                            <span class="{{ $item->is_active ? 'civic-status-success' : 'civic-status-danger' }}">
                                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" @click="openEditItem({{ json_encode($item) }})" class="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 transition hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('dashboard.admin.master-data.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data master {{ $item->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-800 dark:bg-red-950/60 dark:text-red-300">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                            Tidak ada data master yang sesuai filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($items->hasPages())
                        <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal Edit Data Master -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" @keydown.escape.window="editModalOpen = false">
            <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900" @click.outside="editModalOpen = false">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/50">
                    <h3 class="flex items-center gap-2 font-display text-sm font-extrabold text-slate-950 dark:text-white">
                        <span>Edit Data Master</span>
                        <span class="rounded bg-amber-100 px-2 py-0.5 font-mono text-xs text-amber-800 dark:bg-amber-950 dark:text-amber-300" x-text="activeItem ? activeItem.code : ''"></span>
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="rounded-lg p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="activeItem.id ? ('{{ url('dashboard/admin/master-data') }}/' + activeItem.id) : ''" method="POST" class="space-y-3 overflow-y-auto p-5 text-xs">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Grup Master *</label>
                        <select name="group" x-model="activeItem.group" required class="{{ $fieldClass }}">
                            <option value="waste_category">Kategori Sampah (waste_category)</option>
                            <option value="districts">Wilayah Kecamatan (districts)</option>
                            <option value="point_conversion">Konversi Nilai Poin (point_conversion)</option>
                            <option value="complaint_categories">Kategori Aduan (complaint_categories)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Kode Unik *</label>
                            <input type="text" name="code" :value="activeItem ? activeItem.code : ''" required class="{{ $fieldClass }} font-mono">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Urutan</label>
                            <input type="number" name="sort_order" :value="activeItem ? activeItem.sort_order : 0" class="{{ $fieldClass }}">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Nama / Label *</label>
                        <input type="text" name="name" :value="activeItem ? activeItem.name : ''" required class="{{ $fieldClass }}">
                    </div>

                    <div x-show="activeItem && (activeItem.group === 'waste_category' || activeItem.group === 'waste_categories')">
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Pilar Induk Sampah</label>
                        <select name="pillar" :value="activeItem ? (activeItem.pillar || '') : ''" class="{{ $fieldClass }}">
                            <option value="">Otomatis dari Kode</option>
                            <option value="organik">Organik (Kompos / Pakan Maggot)</option>
                            <option value="anorganik_daur_ulang">Anorganik Daur Ulang (Plastik/Kertas/Logam/dll)</option>
                            <option value="residu">Residu (Wajib TPA Winongo)</option>
                        </select>
                    </div>

                    <div x-show="activeItem && activeItem.group !== 'districts' && activeItem.group !== 'complaint_categories'" class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Nilai Numerik</label>
                            <input type="number" step="0.01" name="numeric_value" :value="activeItem ? activeItem.numeric_value : ''" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Satuan Unit</label>
                            <input type="text" name="unit" :value="activeItem ? activeItem.unit : ''" class="{{ $fieldClass }}">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Status Keaktifan</label>
                        <select name="is_active" :value="activeItem ? (activeItem.is_active ? '1' : '0') : '1'" class="{{ $fieldClass }}">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Deskripsi Tambahan</label>
                        <textarea name="description" rows="2" :value="activeItem ? activeItem.description : ''" class="{{ $fieldClass }}"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-3 dark:border-slate-800">
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
