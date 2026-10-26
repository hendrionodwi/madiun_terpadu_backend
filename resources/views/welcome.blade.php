<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="description" content="Platform terpadu untuk pengelolaan sampah dan lingkungan Kota Madiun.">
        @include('partials.head', ['title' => 'Portal Lingkungan Kota Madiun'])
    </head>
    <body class="bg-civic-canvas font-sans text-civic-ink antialiased">
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-civic-canvas/90 backdrop-blur-xl">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-3.5 sm:px-6 lg:px-8" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-civic-950">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-civic-200">
                        <x-app-logo-icon class="size-7" />
                    </span>
                    <span>
                        <span class="block font-display text-sm font-extrabold leading-tight sm:text-base">Madiun Terpadu</span>
                        <span class="block text-[0.58rem] font-bold uppercase tracking-[0.16em] text-civic-700">Kota Madiun</span>
                    </span>
                </a>

                <div class="hidden items-center gap-7 text-sm font-semibold text-slate-600 md:flex">
                    <a href="#layanan" class="hover:text-civic-700">Layanan</a>
                    <a href="#alur" class="hover:text-civic-700">Cara kerja</a>
                    <a href="#ekosistem" class="hover:text-civic-700">Ekosistem</a>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="civic-button-primary">Buka dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden px-3 py-2 text-sm font-bold text-civic-800 hover:text-civic-950 sm:inline-flex">Masuk</a>
                        <a href="{{ route('register') }}" class="civic-button-primary">Daftar warga</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            <section class="relative overflow-hidden bg-civic-950 text-white">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_16%,rgba(101,184,167,0.3),transparent_27%),radial-gradient(circle_at_12%_100%,rgba(217,119,6,0.14),transparent_24%)]"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.07] [background-image:radial-gradient(circle,#fff_1px,transparent_1px)] [background-size:24px_24px]"></div>

                <div class="relative mx-auto grid max-w-7xl gap-14 px-5 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-8 lg:py-24">
                    <div class="max-w-2xl">
                        <p class="civic-kicker">
                            <span class="size-1.5 rounded-full bg-civic-300"></span>
                            Layanan publik Kota Madiun
                        </p>
                        <h1 class="mt-6 font-display text-4xl font-extrabold leading-[1.06] tracking-[-0.045em] sm:text-5xl lg:text-6xl">
                            Dari sampah rumah tangga menjadi
                            <span class="text-civic-300">aksi kota yang terukur.</span>
                        </h1>
                        <p class="mt-6 max-w-xl text-base leading-8 text-civic-100/80 sm:text-lg">
                            Jadwalkan penjemputan, pantau pengolahan, laporkan masalah lingkungan, dan tumbuhkan ekonomi sirkular melalui satu platform terpadu.
                        </p>
                        <div class="mt-9 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-civic-900 shadow-lg shadow-civic-950/25 hover:bg-civic-50">
                                    Buka dashboard
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-civic-900 shadow-lg shadow-civic-950/25 hover:bg-civic-50">
                                    Mulai sebagai warga
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                </a>
                                <a href="#layanan" class="inline-flex items-center rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white backdrop-blur-sm hover:bg-white/15">Jelajahi layanan</a>
                            @endauth
                        </div>
                        <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-xs font-semibold text-civic-100/75 sm:text-sm">
                            <span class="inline-flex items-center gap-2"><span class="flex size-5 items-center justify-center rounded-full bg-civic-500/30 text-civic-200">✓</span> Satu akun terpadu</span>
                            <span class="inline-flex items-center gap-2"><span class="flex size-5 items-center justify-center rounded-full bg-civic-500/30 text-civic-200">✓</span> Status transparan</span>
                            <span class="inline-flex items-center gap-2"><span class="flex size-5 items-center justify-center rounded-full bg-civic-500/30 text-civic-200">✓</span> Data lintas wilayah</span>
                        </div>
                    </div>

                    <div class="relative mx-auto w-full max-w-lg">
                        <div class="absolute -inset-6 rounded-[2.5rem] bg-civic-400/10 blur-2xl"></div>
                        <div class="relative rounded-[2rem] border border-white/15 bg-white/10 p-3 shadow-2xl shadow-civic-950/40 backdrop-blur-sm sm:p-4">
                            <div class="rounded-[1.4rem] bg-slate-50 p-5 text-civic-ink sm:p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-10 items-center justify-center rounded-xl bg-civic-700 text-white">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v18M5.6 6.5 18.4 17.5M18.4 6.5 5.6 17.5"/></svg>
                                        </span>
                                        <div>
                                            <p class="text-[0.6rem] font-extrabold uppercase tracking-[0.15em] text-civic-700">Kota Madiun</p>
                                            <p class="font-display text-sm font-extrabold">Ringkasan Layanan</p>
                                        </div>
                                    </div>
                                    <span class="civic-status-success">Aktif</span>
                                </div>

                                <div class="mt-5 rounded-2xl bg-civic-800 p-5 text-white shadow-lg shadow-civic-900/15">
                                    <p class="text-xs font-semibold text-civic-100/75">Dampak lingkungan bulan ini</p>
                                    <div class="mt-2 flex items-end justify-between gap-4">
                                        <div>
                                            <p class="font-display text-3xl font-extrabold">Terhubung</p>
                                            <p class="mt-1 text-xs text-civic-100/70">Warga · Petugas · TPS3R · DLH</p>
                                        </div>
                                        <svg class="size-12 text-civic-300" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 36c10-1 18-8 20-25 7 6 11 14 9 22-2 8-11 11-18 7-5-3-8-8-8-14"/><path d="M12 38c4-8 10-13 19-17"/></svg>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <span class="civic-icon-tile">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
                                        </span>
                                        <p class="mt-3 text-sm font-extrabold">Jadwal jemput</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500">Pantau armada dan status layanan.</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <span class="flex size-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-7m5 7V8"/></svg>
                                        </span>
                                        <p class="mt-3 text-sm font-extrabold">Data terukur</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500">Rekap volume dan tindak lanjut.</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between rounded-xl border border-civic-200 bg-civic-50 px-4 py-3">
                                    <span class="text-xs font-semibold text-civic-900">Ekosistem layanan lingkungan</span>
                                    <span class="text-xs font-extrabold text-civic-700">5 role terhubung</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="layanan" class="mx-auto max-w-7xl px-5 py-18 sm:px-6 sm:py-22 lg:px-8">
                <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-civic-700">Layanan utama</p>
                        <h2 class="mt-3 font-display text-3xl font-extrabold tracking-[-0.035em] text-civic-950 sm:text-4xl">Satu ekosistem, alur kerja yang lebih ringkas.</h2>
                    </div>
                    <p class="max-w-md text-sm leading-7 text-slate-600">Setiap layanan dirancang untuk membuat pencatatan, pemantauan, dan koordinasi antarpihak lebih mudah.</p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Penjemputan', 'Ajukan dan pantau penjemputan sampah terpilah dari rumah.', 'truck'],
                        ['Timbang & poin', 'Catat hasil timbang dan lihat perolehan reward warga.', 'scale'],
                        ['Marketplace', 'Temukan produk olahan dan karya daur ulang TPS3R.', 'bag'],
                        ['Pengaduan', 'Laporkan persoalan lingkungan dan pantau tindak lanjut.', 'report'],
                    ] as [$serviceTitle, $serviceDescription, $serviceIcon])
                        <article class="civic-card-interactive p-5">
                            <span class="civic-icon-tile">
                                @if ($serviceIcon === 'truck')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
                                @elseif ($serviceIcon === 'scale')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3v18M5 6h14M5 6l-3 7h6L5 6Zm14 0-3 7h6l-3-7ZM8 21h8"/></svg>
                                @elseif ($serviceIcon === 'bag')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg>
                                @else
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z"/><path d="M12 7v6M12 17h.01"/></svg>
                                @endif
                            </span>
                            <h3 class="mt-5 font-display text-lg font-extrabold text-civic-950">{{ $serviceTitle }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $serviceDescription }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="alur" class="border-y border-civic-200/70 bg-civic-100/70">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 py-18 sm:px-6 sm:py-22 lg:grid-cols-[0.75fr_1.25fr] lg:items-center lg:px-8">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-civic-700">Cara kerja</p>
                        <h2 class="mt-3 font-display text-3xl font-extrabold tracking-[-0.035em] text-civic-950">Mulai dari satu akun, lanjutkan sesuai peran.</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-600">Hak akses dan menu menyesuaikan tanggung jawab setiap pengguna tanpa memutus aliran data antarunit.</p>
                    </div>
                    <ol class="grid gap-4 sm:grid-cols-3">
                        @foreach ([['01', 'Daftar atau masuk', 'Gunakan identitas akun layanan yang telah disiapkan.'], ['02', 'Pilih layanan', 'Akses menu yang relevan dengan kebutuhan dan role.'], ['03', 'Pantau progres', 'Lihat status, riwayat, dan tindak lanjut secara transparan.']] as [$stepNumber, $stepTitle, $stepDescription])
                            <li class="rounded-2xl border border-civic-200 bg-white p-5 shadow-[0_12px_28px_-24px_rgba(4,106,88,0.4)]">
                                <span class="text-xs font-extrabold tracking-[0.18em] text-civic-700">{{ $stepNumber }}</span>
                                <h3 class="mt-5 font-display font-extrabold text-civic-950">{{ $stepTitle }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $stepDescription }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <section id="ekosistem" class="mx-auto max-w-7xl px-5 py-18 sm:px-6 sm:py-22 lg:px-8">
                <div class="rounded-[2rem] bg-civic-900 px-6 py-10 text-white shadow-[0_24px_65px_-38px_rgba(3,40,35,0.8)] sm:px-10 sm:py-12 lg:flex lg:items-center lg:justify-between lg:gap-14">
                    <div class="max-w-2xl">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-civic-300">Ekosistem terpadu</p>
                        <h2 class="mt-3 font-display text-3xl font-extrabold tracking-[-0.035em]">Lingkungan bersih adalah kerja bersama.</h2>
                        <p class="mt-4 text-sm leading-7 text-civic-100/75 sm:text-base">Warga, petugas lapangan, pengelola TPS3R, pemrakarsa, dan Admin DLH bekerja melalui data dan proses yang saling terhubung.</p>
                    </div>
                    <div class="mt-8 flex shrink-0 flex-wrap gap-2 lg:mt-0 lg:max-w-sm lg:justify-end">
                        @foreach (['Warga', 'Petugas', 'TPS3R', 'Pemrakarsa', 'Admin DLH'] as $role)
                            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-civic-100">{{ $role }}</span>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-7 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>© {{ now()->year }} Dinas Lingkungan Hidup Kota Madiun.</p>
                <div class="flex items-center gap-5">
                    <a href="#layanan" class="font-semibold text-civic-700 hover:text-civic-900">Layanan</a>
                    <a href="{{ route('login') }}" class="font-semibold text-civic-700 hover:text-civic-900">Portal pengguna</a>
                </div>
            </div>
        </footer>
    </body>
</html>
