<section class="mt-14 bg-[#172536] text-white" data-scroll-fade>
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:px-8">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-civic-300">Aksi Bersama Kota Madiun</p>
            <h2 class="mt-2 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                Ambil Bagian dalam Lingkungan Kota Madiun
            </h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                Mulai dari memilah 3 wadah di rumah, memanfaatkan penjemputan berkala, hingga mendukung sirkularitas lokal dengan menyerap produk olahan binaan Dinas Lingkungan Hidup.
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-civic-600 px-4 text-sm font-bold text-white shadow-sm hover:bg-civic-500">
                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1" /><rect width="7" height="5" x="14" y="3" rx="1" /><rect width="7" height="9" x="14" y="12" rx="1" /><rect width="7" height="5" x="3" y="16" rx="1" /></svg>
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-civic-600 px-4 text-sm font-bold text-white shadow-sm hover:bg-civic-500">
                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><line x1="19" x2="19" y1="8" y2="14" /><line x1="22" x2="16" y1="11" y2="11" /></svg>
                    Daftar sebagai Warga
                </a>
            @endauth

            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/8 px-4 text-sm font-bold text-white hover:border-white/30 hover:bg-white/15">
                <svg class="size-4 shrink-0 text-civic-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 17h4V5H2v12h3m15 0h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5v8h1" /><circle cx="7.5" cy="17.5" r="2.5" /><circle cx="17.5" cy="17.5" r="2.5" /></svg>
                Ajukan Penjemputan
            </a>

            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-4 text-sm font-semibold text-slate-100 hover:border-white/30 hover:bg-white/12">
                <svg class="size-4 shrink-0 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10" /><path d="M12 8v4M12 16h.01" /></svg>
                Laporkan Masalah Lingkungan
            </a>

            <a href="{{ route('public.services') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-4 text-sm font-semibold text-slate-100 hover:border-civic-400/50 hover:bg-civic-900/40">
                <svg class="size-4 shrink-0 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z" /><path d="M9 3v15M15 6v15" /></svg>
                Kunjungi TPS3R Terdekat
            </a>
        </div>
    </div>
</section>
