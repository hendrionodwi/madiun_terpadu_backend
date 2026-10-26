<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="civic-app-main">
        <div class="mx-auto w-full max-w-[1680px]">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
