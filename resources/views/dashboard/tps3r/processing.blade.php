<x-layouts::app :title="__('Pengolahan Sampah')">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6">
        <div>
            <p class="text-sm font-semibold text-teal-700 dark:text-teal-400">Operasional TPS3R</p>
            <h1 class="text-2xl font-bold text-zinc-950 dark:text-white">Proses Pengolahan Sampah</h1>
            <p class="mt-1 text-sm text-zinc-500">Catat batch dari bahan masuk hingga hasil olahan selesai, terintegrasi dengan Data Master.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Pembuatan Batch Pengolahan (Auto-sync dengan Setoran & Data Master) -->
        <form method="POST" action="{{ route('dashboard.tps3r.processing.store') }}" 
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
              class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-3">
            @csrf

            <label class="grid gap-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                <span class="flex items-center justify-between">
                    <span>Setoran sumber</span>
                    <span class="text-[11px] text-teal-600 dark:text-teal-400 font-normal">Otomatis isi berat & jenis</span>
                </span>
                <select name="tps3r_waste_deposit_id" x-model="selectedDepositId" @change="onDepositChange()" class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">Tanpa referensi setoran (Input Manual)</option>
                    @foreach ($deposits as $deposit)
                        <option value="{{ $deposit->id }}">
                            {{ $deposit->nomor_resi }} · {{ number_format($deposit->berat_kg, 1, ',', '.') }} kg ({{ ucwords(str_replace('_', ' ', $deposit->jenis_sampah)) }})
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="grid gap-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                <span>Metode pengolahan</span>
                <select name="process_method" required class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    @foreach ($processMethods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="grid gap-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                <span class="flex items-center justify-between">
                    <span>Jenis sampah</span>
                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-normal">Data Master</span>
                </span>
                <select name="waste_type" x-model="wasteType" required class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">Pilih jenis sampah</option>
                    <optgroup label="3 Pilar Utama Sampah Kota Madiun">
                        <option value="organik">Organik (Pakan Maggot / Kompos)</option>
                        <option value="anorganik_daur_ulang">Anorganik Daur Ulang (Plastik / Logam / Kertas)</option>
                        <option value="residu">Residu (Kirim TPA Winongo)</option>
                    </optgroup>
                    @if (isset($masterCategories) && $masterCategories->isNotEmpty())
                        <optgroup label="Kategori Sampah Data Master DLH">
                            @foreach ($masterCategories as $cat)
                                <option value="{{ $cat->code }}">
                                    {{ $cat->name }} (Pilar: {{ ucfirst(str_replace('_', ' ', $cat->pillar ?? 'umum')) }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </label>

            <label class="grid gap-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                <span>Berat masuk (kg)</span>
                <input type="number" step="0.01" min="0.1" name="input_weight_kg" x-model="inputWeight" required placeholder="0.0" class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
            </label>

            <label class="grid gap-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                <span>Tanggal mulai</span>
                <input type="date" name="started_on" value="{{ now()->toDateString() }}" required class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
            </label>

            <label class="grid gap-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                <span>Catatan</span>
                <input type="text" name="notes" placeholder="Catatan proses (opsional)" class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
            </label>

            <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 md:col-span-3 dark:bg-teal-600 dark:hover:bg-teal-700">
                Buat batch pengolahan
            </button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3">Batch</th>
                            <th class="px-4 py-3">Proses</th>
                            <th class="px-4 py-3">Berat</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($records as $record)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100">#{{ $record->id }}</p>
                                    <p class="text-xs text-zinc-500">
                                        {{ $record->deposit?->nomor_resi ?? 'Tanpa resi' }} · {{ $record->started_on->format('d M Y') }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-zinc-800 dark:text-zinc-200">
                                    <p class="font-semibold text-teal-800 dark:text-teal-300">
                                        {{ $record->process_method_label }}
                                    </p>
                                    <p class="text-xs text-zinc-500 font-medium mt-0.5">
                                        🏷️ {{ $record->waste_type_label }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-zinc-800 dark:text-zinc-200">
                                    <span class="font-medium">Masuk: {{ number_format($record->input_weight_kg, 1, ',', '.') }} kg</span>
                                    @if ($record->output_weight_kg !== null)
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                                            ✓ Hasil: {{ number_format($record->output_weight_kg, 1, ',', '.') }} kg
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @php
                                        $badgeStyles = match ($record->status) {
                                            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                            'processing' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                            'planned' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                            'cancelled' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 line-through',
                                            default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                        };
                                        $statusLabels = [
                                            'planned' => 'Direncanakan',
                                            'processing' => 'Diproses',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                        ];
                                    @endphp
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wider {{ $badgeStyles }}">
                                        {{ $statusLabels[$record->status] ?? $record->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($record->status === 'planned')
                                        {{-- Transisi dari Planned: hanya ke Processing atau Cancelled --}}
                                        <form method="POST" action="{{ route('dashboard.tps3r.processing.update', $record) }}" class="flex min-w-64 items-center gap-2">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="rounded-lg border border-zinc-300 px-2.5 py-1.5 text-xs outline-none focus:border-teal-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="processing">Mulai Proses</option>
                                                <option value="cancelled">Batalkan</option>
                                            </select>

                                            <button type="submit" class="rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-teal-800 dark:bg-teal-600 dark:hover:bg-teal-700">
                                                Simpan
                                            </button>
                                        </form>
                                    @elseif ($record->status === 'processing')
                                        {{-- Transisi dari Processing: ke Completed (wajib hasil kg) atau Cancelled --}}
                                        <form method="POST" action="{{ route('dashboard.tps3r.processing.update', $record) }}" class="flex min-w-72 items-center gap-2">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="rounded-lg border border-zinc-300 px-2.5 py-1.5 text-xs outline-none focus:border-teal-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="completed">Selesaikan</option>
                                                <option value="cancelled">Batalkan</option>
                                            </select>

                                            <input type="number" step="0.01" min="0" name="output_weight_kg" placeholder="Hasil kg" class="w-24 rounded-lg border border-zinc-300 px-2.5 py-1.5 text-xs outline-none focus:border-teal-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">

                                            <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-800 dark:bg-emerald-600 dark:hover:bg-emerald-700">
                                                Selesai
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-zinc-400 font-semibold italic">Arsip Final</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-zinc-500">
                                    Belum ada batch pengolahan sampah tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
