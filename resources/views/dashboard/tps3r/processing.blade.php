<x-layouts::app :title="__('Pengolahan Sampah')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div class="civic-page mx-auto w-full max-w-7xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Pengolahan Sampah"
            eyebrow="Operasional TPS3R"
            title="Proses Pengolahan Sampah"
            description="Catat batch dari bahan masuk hingga hasil olahan selesai, terintegrasi dengan Data Master."
            icon="arrow-path-rounded-square"
        />

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

        <!-- Form Pembuatan Batch Pengolahan (Auto-sync dengan Setoran & Data Master) -->
        <form
            method="POST"
            action="{{ route('dashboard.tps3r.processing.store') }}"
            x-data="{
                selectedDepositId: '',
                deposits: {{ Js::from($deposits->keyBy('id')) }},
                inputWeight: '{{ old('input_weight_kg', '') }}',
                wasteType: '{{ old('waste_type', '') }}',
                onDepositChange() {
                    if (this.selectedDepositId && this.deposits[this.selectedDepositId]) {
                        const dep = this.deposits[this.selectedDepositId];
                        this.inputWeight = dep.berat_kg;
                        this.wasteType = dep.jenis_sampah;
                    }
                }
            }"
            class="civic-card grid gap-4 p-5 sm:p-6 md:grid-cols-3"
        >
            @csrf

            <label class="grid min-w-0 gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                <span>Setoran sumber</span>
                <select name="tps3r_waste_deposit_id" x-model="selectedDepositId" @change="onDepositChange()" class="{{ $fieldClass }} truncate">
                    <option value="">Tanpa referensi (Manual / Stok)</option>
                    @foreach ($deposits as $deposit)
                        <option value="{{ $deposit->id }}" title="{{ $deposit->nomor_resi }} ({{ number_format($deposit->berat_kg, 1, ',', '.') }} kg)">
                            {{ $deposit->nomor_resi }} ({{ number_format($deposit->berat_kg, 1, ',', '.') }} kg)
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="grid min-w-0 gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                <span>Metode pengolahan</span>
                <select name="process_method" required class="{{ $fieldClass }} truncate">
                    @foreach ($processMethods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="grid min-w-0 gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                <span>Jenis sampah</span>
                <select name="waste_type" x-model="wasteType" required class="{{ $fieldClass }} truncate">
                    <option value="">Pilih jenis sampah</option>
                    <optgroup label="3 Pilar Utama">
                        <option value="organik">Organik</option>
                        <option value="anorganik_daur_ulang">Anorganik Daur Ulang</option>
                        <option value="residu">Residu</option>
                    </optgroup>
                    @if (isset($masterCategories) && $masterCategories->isNotEmpty())
                        <optgroup label="Data Master">
                            @foreach ($masterCategories as $cat)
                                <option value="{{ $cat->code }}" title="{{ $cat->name }} (Pilar: {{ ucfirst($cat->pillar ?? 'umum') }})">
                                    {{ \Illuminate\Support\Str::limit($cat->name, 26) }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </label>

            <!-- Panel Pintar Rincian Fraksi Muatan Truk -->
            <div x-show="selectedDepositId && deposits[selectedDepositId]" x-cloak class="rounded-2xl border border-civic-200 bg-civic-50/80 p-3.5 text-xs md:col-span-3 dark:border-civic-900 dark:bg-civic-950/30">
                <div class="flex flex-col justify-between gap-2.5 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2 text-civic-950 dark:text-civic-100">
                        <flux:icon.truck class="size-5 shrink-0 text-civic-700 dark:text-civic-300" />
                        <div>
                            <span class="font-bold">Muatan Truk #<span x-text="deposits[selectedDepositId]?.nomor_resi"></span>:</span>
                            <span class="text-slate-600 dark:text-slate-300">Total berat terdata <strong x-text="Number(deposits[selectedDepositId]?.berat_kg || 0).toLocaleString('id-ID', {minimumFractionDigits: 1})"></strong> kg.</span>
                            <span class="block text-xs text-civic-700 dark:text-civic-300">Pilih fraksi pilah di bawah untuk mengisi otomatis, atau edit berat masuk secara fleksibel:</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5">
                        <template x-if="deposits[selectedDepositId]?.berat_organik_pilah_kg > 0">
                            <button type="button" @click="wasteType = 'organik'; inputWeight = deposits[selectedDepositId].berat_organik_pilah_kg" class="inline-flex items-center gap-1 rounded-lg bg-civic-700 px-2.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:bg-civic-800">
                                <flux:icon.sparkles class="size-3.5" /> Fraksi Organik (<span x-text="deposits[selectedDepositId].berat_organik_pilah_kg"></span> kg)
                            </button>
                        </template>
                        <template x-if="deposits[selectedDepositId]?.berat_anorganik_pilah_kg > 0">
                            <button type="button" @click="wasteType = 'anorganik_daur_ulang'; inputWeight = deposits[selectedDepositId].berat_anorganik_pilah_kg" class="inline-flex items-center gap-1 rounded-lg bg-sky-700 px-2.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:bg-sky-800">
                                <flux:icon.arrow-path-rounded-square class="size-3.5" /> Fraksi Anorganik (<span x-text="deposits[selectedDepositId].berat_anorganik_pilah_kg"></span> kg)
                            </button>
                        </template>
                        <template x-if="deposits[selectedDepositId]?.berat_residu_pilah_kg > 0">
                            <button type="button" @click="wasteType = 'residu'; inputWeight = deposits[selectedDepositId].berat_residu_pilah_kg" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-bold text-white shadow-xs transition hover:bg-red-700">
                                <flux:icon.trash class="size-3.5" /> Fraksi Residu (<span x-text="deposits[selectedDepositId].berat_residu_pilah_kg"></span> kg)
                            </button>
                        </template>
                        <button type="button" @click="inputWeight = deposits[selectedDepositId]?.berat_kg || ''; wasteType = deposits[selectedDepositId]?.jenis_sampah || ''" class="civic-button-secondary !min-h-8 !px-2.5 !py-1 !text-xs">
                            Semua Muatan (<span x-text="deposits[selectedDepositId]?.berat_kg"></span> kg)
                        </button>
                    </div>
                </div>
            </div>

            <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                <span>Berat masuk (kg)</span>
                <input type="number" step="0.01" min="0.1" name="input_weight_kg" x-model="inputWeight" required placeholder="0.0" class="{{ $fieldClass }}">
            </label>

            <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                <span>Tanggal mulai</span>
                <input type="date" name="started_on" value="{{ now()->toDateString() }}" required class="{{ $fieldClass }}">
            </label>

            <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                <span>Catatan</span>
                <input type="text" name="notes" placeholder="Catatan proses (opsional)" class="{{ $fieldClass }}">
            </label>

            <button type="submit" class="civic-button-primary md:col-span-3">
                Buat batch pengolahan
            </button>
        </form>

        <div data-ajax-zone="tps3r-processing" class="civic-card overflow-hidden">
            <div class="civic-table-wrap rounded-none border-0">
                <table class="civic-table">
                    <thead>
                        <tr>
                            <th>Batch</th>
                            <th>Proses</th>
                            <th>Berat</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $badgeStyles = match ($record->status) {
                                    'completed' => 'civic-status-success',
                                    'processing' => 'civic-status-warning',
                                    'planned' => 'civic-status-info',
                                    'cancelled' => 'civic-status bg-slate-100 text-slate-500 line-through dark:bg-slate-800 dark:text-slate-400',
                                    default => 'civic-status bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                };
                                $statusLabels = [
                                    'planned' => 'Direncanakan',
                                    'processing' => 'Diproses',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <p class="font-bold text-slate-950 dark:text-white">#{{ $record->id }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $record->deposit?->nomor_resi ?? 'Tanpa resi' }} · {{ $record->started_on->format('d M Y') }}
                                    </p>
                                </td>
                                <td class="text-slate-800 dark:text-slate-200">
                                    <p class="font-semibold text-civic-800 dark:text-civic-300">
                                        {{ $record->process_method_label }}
                                    </p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                        <span class="inline-flex items-center gap-1"><flux:icon.tag class="size-3.5" /> {{ $record->waste_type_label }}</span>
                                    </p>
                                </td>
                                <td class="text-slate-800 dark:text-slate-200">
                                    <span class="font-medium">Masuk: {{ number_format($record->input_weight_kg, 1, ',', '.') }} kg</span>
                                    @if ($record->output_weight_kg !== null)
                                        <p class="mt-0.5 text-xs font-bold text-civic-700 dark:text-civic-300">
                                            ✓ Hasil: {{ number_format($record->output_weight_kg, 1, ',', '.') }} kg
                                        </p>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $badgeStyles }} uppercase tracking-wider">
                                        {{ $statusLabels[$record->status] ?? $record->status }}
                                    </span>
                                </td>
                                <td>
                                    @if ($record->status === 'planned')
                                        <form method="POST" action="{{ route('dashboard.tps3r.processing.update', $record) }}" class="flex min-w-64 items-center gap-2">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="min-h-9 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 outline-none focus:border-civic-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                                <option value="processing">Mulai Proses</option>
                                                <option value="cancelled">Batalkan</option>
                                            </select>

                                            <button type="submit" class="civic-button-primary !min-h-9 !px-3 !py-1.5 !text-xs">
                                                Simpan
                                            </button>
                                        </form>
                                    @elseif ($record->status === 'processing')
                                        <form method="POST" action="{{ route('dashboard.tps3r.processing.update', $record) }}" class="flex min-w-72 items-center gap-2">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="min-h-9 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 outline-none focus:border-civic-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                                <option value="completed">Selesaikan</option>
                                                <option value="cancelled">Batalkan</option>
                                            </select>

                                            <input type="number" step="0.01" min="0" name="output_weight_kg" placeholder="Hasil kg" class="min-h-9 w-24 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 outline-none focus:border-civic-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">

                                            <button type="submit" class="civic-button-primary !min-h-9 !px-3 !py-1.5 !text-xs">
                                                Selesai
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold italic text-slate-400">Arsip Final</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada batch pengolahan sampah tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
