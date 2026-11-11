@props([
    'complaint',
    'activeRoutes' => collect(),
])

<div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
    <label class="min-w-0 space-y-1.5">
        <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Prioritas</span>
        <select name="prioritas" class="min-h-10 w-full rounded-xl border border-blue-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
            @foreach (['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi', 'darurat' => 'Darurat'] as $value => $label)
                <option value="{{ $value }}" @selected(($complaint->prioritas ?: 'sedang') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="min-w-0 space-y-1.5">
        <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Rute terkait</span>
        <select name="route_code" class="min-h-10 w-full rounded-xl border border-blue-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100">
            <option value="">Tanpa rute tetap</option>
            @foreach ($activeRoutes as $route)
                <option value="{{ $route->route_code }}" @selected($complaint->route_code === $route->route_code)>
                    {{ $route->route_code }} · {{ $route->route_name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="min-w-0 space-y-1.5">
        <span class="block font-semibold text-zinc-700 dark:text-zinc-300">Target selesai</span>
        <input
            type="datetime-local"
            name="target_selesai_at"
            value="{{ $complaint->target_selesai_at?->format('Y-m-d\TH:i') }}"
            class="min-h-10 w-full rounded-xl border border-blue-200 bg-white px-3 py-2 font-semibold text-zinc-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-blue-900 dark:bg-zinc-900 dark:text-zinc-100"
        >
    </label>
</div>
