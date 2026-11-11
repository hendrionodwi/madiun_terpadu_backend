<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="description" content="Status pembayaran Madiun Terpadu sedang disinkronkan.">
        @include('partials.head', ['title' => 'Pembayaran Diproses'])
    </head>
    <body class="min-h-screen bg-civic-canvas font-sans text-civic-ink antialiased">
        <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-5 py-10 sm:px-6">
            <div class="pointer-events-none absolute inset-0 -z-20 bg-[radial-gradient(circle_at_18%_10%,rgba(4,106,88,0.18),transparent_30%),radial-gradient(circle_at_82%_85%,rgba(217,119,6,0.12),transparent_28%)]"></div>
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.35] [background-image:radial-gradient(circle,rgba(15,23,42,0.13)_1px,transparent_1px)] [background-size:24px_24px]"></div>

            <section class="w-full max-w-xl overflow-hidden rounded-[2rem] border border-white/80 bg-white/90 shadow-2xl shadow-civic-950/15 backdrop-blur-xl">
                <div class="bg-civic-900 px-6 py-7 text-white sm:px-9 sm:py-9">
                    <div class="flex items-center gap-3">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm">
                            <x-app-logo-icon class="size-7" />
                        </span>
                        <div>
                            <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.18em] text-civic-200">Kota Madiun</p>
                            <p class="font-display text-base font-extrabold">Madiun Terpadu</p>
                        </div>
                    </div>

                    <div class="mt-8 flex size-14 items-center justify-center rounded-2xl bg-civic-400/20 text-civic-200 ring-1 ring-inset ring-white/10">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </div>
                    <h1 class="mt-5 font-display text-3xl font-extrabold tracking-[-0.035em]">Pembayaran telah diproses</h1>
                    <p class="mt-3 max-w-md text-sm leading-6 text-civic-100/80">
                        Midtrans telah menyelesaikan proses pembayaran. Status pesanan akan diperbarui melalui notifikasi aman dari server.
                    </p>
                </div>

                <div class="px-6 py-7 sm:px-9 sm:py-8">
                    <div class="rounded-2xl border border-civic-200 bg-civic-50 p-4">
                        <div class="flex gap-3">
                            <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-xl bg-civic-100 text-civic-800">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 8v4l3 2" /><circle cx="12" cy="12" r="9" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-sm font-extrabold text-civic-950">Periksa status di aplikasi</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    Kembali ke Madiun Terpadu, lalu tekan <strong>Periksa status pembayaran</strong>. Pembaruan webhook dapat memerlukan beberapa detik.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="mt-5 text-xs leading-5 text-slate-500">
                        Anda boleh menutup tab ini. Jangan membagikan tautan pembayaran atau informasi transaksi kepada pihak lain.
                    </p>

                    <a href="{{ route('home') }}" class="civic-button-primary mt-6 w-full justify-center">
                        Buka portal Madiun Terpadu
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                </div>
            </section>
        </main>
    </body>
</html>
