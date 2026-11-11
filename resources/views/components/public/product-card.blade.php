@props(['product'])

<article {{ $attributes->class('portal-interactive-card group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white') }}>
    <div>
        <div class="relative aspect-square overflow-hidden bg-civic-50">
            @if ($product->foto_url)
                <img src="{{ $product->foto_url }}" alt="{{ $product->nama_produk }}" class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.03]">
            @else
                <div class="flex h-full items-center justify-center text-civic-600">
                    <svg class="size-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path d="M8 13c4 0 6-2 7-6 2 5 0 10-5 11" /></svg>
                </div>
            @endif

            <div class="absolute inset-x-3 top-3 flex flex-wrap items-center justify-between gap-1.5">
                <span class="rounded-full border border-civic-200/80 bg-white/95 px-2.5 py-0.5 text-[0.7rem] font-bold text-civic-800 shadow-xs">
                    {{ str($product->kategori)->replace('_', ' ')->title() }}
                </span>
                @if ($product->stok > 0 && $product->stok <= 5)
                    <span class="rounded-full bg-amber-100/95 px-2.5 py-0.5 text-[0.68rem] font-bold text-amber-900 shadow-xs">Stok Terbatas</span>
                @endif
            </div>
        </div>

        <div class="p-4">
            <p class="flex items-center gap-1.5 truncate text-xs font-semibold text-slate-500">
                <svg class="size-3.5 shrink-0 text-civic-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9l2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.79 1.1L21 9M3 9h18M5 9v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9" /></svg>
                <span class="truncate">{{ $product->seller->pengelolaTps3rProfile?->nama_tps3r ?? $product->seller->name }}</span>
            </p>
            <h3 class="mt-1.5 line-clamp-2 font-display text-base font-extrabold leading-snug text-slate-950 group-hover:text-civic-800">{{ $product->nama_produk }}</h3>

            <div class="mt-3 flex items-baseline justify-between gap-2 tabular-nums">
                <p class="font-display text-lg font-extrabold text-civic-800">Rp{{ number_format($product->harga, 0, ',', '.') }}</p>
                <p class="text-xs font-medium text-slate-500">Stok: <span class="font-bold text-slate-700">{{ number_format($product->stok, 0, ',', '.') }}</span></p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 border-t border-slate-100 bg-slate-50/60 p-3">
        @if ($product->seller->pengelolaTps3rProfile)
            <a href="{{ route('public.marketplace', ['store' => $product->seller->pengelolaTps3rProfile->public_id]) }}" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 hover:border-civic-300 hover:text-civic-800">
                Lihat Toko
            </a>
        @else
            <a href="{{ route('public.marketplace') }}" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 hover:border-civic-300 hover:text-civic-800">
                Detail
            </a>
        @endif

        @auth
            <a href="{{ route('dashboard') }}" class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-civic-700 text-white hover:bg-civic-800" aria-label="Buka marketplace" title="Beli di dashboard">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z" /><path d="M9 9V6a3 3 0 0 1 6 0v3" /></svg>
            </a>
        @else
            <a href="{{ route('login') }}" class="inline-flex min-h-9 shrink-0 items-center gap-1.5 rounded-lg bg-civic-700 px-3 text-xs font-bold text-white hover:bg-civic-800" title="Masuk untuk membeli">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z" /><path d="M9 9V6a3 3 0 0 1 6 0v3" /></svg>
                Beli
            </a>
        @endauth
    </div>
</article>
