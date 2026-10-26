@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <div class="inline-flex items-center justify-center gap-1.5 self-center rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-3 py-1 text-xs font-semibold text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 mb-2">
        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        DLH Kota Madiun
    </div>
    <flux:heading size="xl" level="1" class="font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ $title }}</flux:heading>
    <flux:subheading class="text-zinc-600 dark:text-zinc-400 text-sm mt-1">{{ $description }}</flux:subheading>
</div>
