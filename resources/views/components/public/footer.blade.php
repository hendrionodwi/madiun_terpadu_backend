@props(['portalSettings' => []])

@php
    $address = $portalSettings['contact_address'] ?? 'Jl. Kolonel Marhadi No. 1, Kota Madiun, Jawa Timur 63117';
    $email = $portalSettings['contact_email'] ?? 'dlh@madiunkota.go.id';
    $phone = $portalSettings['contact_phone'] ?? '(0351) 462748';
    $transparencyNote = $portalSettings['transparency_note']
        ?? 'Portal ini menyajikan data operasional resmi Dinas Lingkungan Hidup Kota Madiun untuk keterbukaan dan layanan publik terpadu.';
@endphp

<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.35fr_0.8fr_0.95fr] lg:px-8">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-civic-50 text-civic-700 ring-1 ring-civic-200/80">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M3 21h18M5 21V9l7-4 7 4v12M9 21v-6h6v6M9 11h.01M15 11h.01" />
                    </svg>
                </span>
                <div>
                    <p class="font-display text-sm font-extrabold text-civic-800">Pemerintah Kota Madiun</p>
                    <p class="text-xs font-medium text-slate-500">Dinas Lingkungan Hidup</p>
                </div>
            </div>

            <p class="mt-4 max-w-sm text-sm leading-6 text-slate-600">
                Portal resmi tata kelola persampahan dan perlindungan lingkungan hidup terpadu menuju Kota Madiun yang bersih, mandiri, sirkular, dan berkelanjutan.
            </p>

            <div class="mt-5 space-y-2 text-xs font-medium text-slate-600">
                <p class="flex items-start gap-2.5">
                    <svg class="mt-0.5 size-4 shrink-0 text-civic-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" /><circle cx="12" cy="10" r="2.5" /></svg>
                    <span>{{ $address }}</span>
                </p>
                <p class="flex items-center gap-2.5">
                    <svg class="size-4 shrink-0 text-civic-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" /></svg>
                    <span>{{ $email }}</span>
                </p>
                <p class="flex items-center gap-2.5">
                    <svg class="size-4 shrink-0 text-civic-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z" /></svg>
                    <span class="tabular-nums">{{ $phone }}</span>
                </p>
            </div>
        </div>

        <div class="text-sm">
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-slate-900">Layanan &amp; Navigasi</p>
            <div class="mt-4 grid gap-2.5 text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-civic-700">Beranda</a>
                <a href="{{ route('public.services') }}" class="hover:text-civic-700">Jadwal &amp; Rute Armada</a>
                <a href="{{ route('public.services') }}" class="hover:text-civic-700">Jaringan TPS3R &amp; Bank Sampah</a>
                <a href="{{ route('public.marketplace') }}" class="hover:text-civic-700">Marketplace Daur Ulang</a>
            </div>
        </div>

        <div class="text-sm">
            <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-slate-900">Keterbukaan Publik</p>
            <div class="mt-4 grid gap-2.5 text-slate-600">
                <a href="{{ route('public.education') }}" class="hover:text-civic-700">Edukasi &amp; Publikasi Lingkungan</a>
                <a href="{{ route('public.education') }}" class="hover:text-civic-700">Panduan &amp; Regulasi Resmi</a>
                <a href="{{ route('login') }}" class="hover:text-civic-700">Masuk Layanan Terpadu</a>
                <a href="{{ route('register') }}" class="hover:text-civic-700">Pendaftaran Akun Warga</a>
            </div>

            <div class="mt-5 rounded-xl border border-indigo-100 bg-slate-50 p-3.5 text-xs leading-5 text-slate-600">
                <span class="font-bold text-slate-800">Catatan:</span> {{ $transparencyNote }}
            </div>
        </div>
    </div>

    <div class="border-t border-slate-100 bg-slate-50/70 px-4 py-4">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 text-xs text-slate-500 sm:flex-row sm:px-2 lg:px-4">
            <p>&copy; {{ now()->year }} Pemerintah Kota Madiun. Dinas Lingkungan Hidup.</p>
            <span class="inline-flex items-center rounded-full border border-civic-200 bg-civic-50 px-3 py-1 font-bold text-civic-800">
                Portal Resmi DLH Kota Madiun
            </span>
        </div>
    </div>
</footer>
