<x-layouts::app :title="__('Pengaturan Pembayaran Midtrans TPS3R')">
    <div x-data="{
        copiedWebhook: false,
        activeTab: 'tutorial',
        testingConnection: false,
        testResult: null,
        testErrorMessage: '',
        copyWebhook() {
            navigator.clipboard.writeText('{{ $webhookUrl }}');
            this.copiedWebhook = true;
            setTimeout(() => this.copiedWebhook = false, 3000);
        },
        async testMidtrans() {
            this.testingConnection = true;
            this.testResult = null;
            this.testErrorMessage = '';
            try {
                const response = await fetch('{{ route('dashboard.tps3r.payment-settings.test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        server_key: document.getElementById('input_server_key').value,
                        is_production: document.getElementById('input_is_production').value === '1'
                    })
                });
                const data = await response.json();
                this.testResult = data;
            } catch (err) {
                this.testErrorMessage = 'Terjadi kesalahan jaringan saat menguji koneksi Midtrans.';
            } finally {
                this.testingConnection = false;
            }
        }
    }" class="mx-auto flex max-w-7xl flex-col gap-6 p-2 md:p-4">

        {{-- Header Page --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-md bg-teal-100 p-1.5 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </span>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Gateway Pembayaran Midtrans Mandiri</h1>
                </div>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Konfigurasi akun Midtrans milik TPS3R Anda agar uang pembayaran pesanan warga (QRIS, Transfer Bank, E-Wallet) langsung masuk ke rekening bank fasilitas Anda.
                </p>
            </div>
            <a href="{{ route('dashboard.tps3r.orders') }}" class="inline-flex w-fit items-center gap-1.5 rounded-lg border border-zinc-300 px-3.5 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                &larr; Lihat Pesanan Masuk
            </a>
        </div>

        {{-- Alerts --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">
                <div class="flex items-center gap-2">
                    <flux:icon.check-circle class="size-5 shrink-0" />
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">
                <p class="font-bold">Periksa kembali masukan Anda:</p>
                <ul class="mt-1.5 list-inside list-disc space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Admin DLH Inspector Selector --}}
        @if ($isAdmin)
            <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Mode Monitoring Admin DLH</span>
                        <p class="text-sm text-amber-900 dark:text-amber-200">Anda dapat melihat status konfigurasi Midtrans untuk fasilitas TPS3R di bawah ini:</p>
                    </div>
                    <form method="GET" action="{{ route('dashboard.tps3r.payment-settings') }}" class="flex items-center gap-2">
                        <select name="tps3r_user_id" onchange="this.form.submit()" class="rounded-lg border-amber-300 bg-white text-xs text-zinc-800 dark:border-amber-800 dark:bg-zinc-900 dark:text-zinc-200">
                            @foreach ($allTps3rList as $tpsItem)
                                <option value="{{ $tpsItem->user_id }}" {{ $profile?->user_id === $tpsItem->user_id ? 'selected' : '' }}>
                                    {{ $tpsItem->nama_tps3r }} ({{ $tpsItem->user?->name ?? 'User' }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        @endif

        {{-- Status Overview Card --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Status Pembayaran Online</p>
                <div class="mt-2 flex items-center gap-2">
                    @if ($profile?->isMidtransConfigured())
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="text-base font-bold text-emerald-700 dark:text-emerald-400">Aktif & Siap Menerima Pembayaran</span>
                    @else
                        <span class="h-3 w-3 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                        <span class="text-base font-semibold text-zinc-600 dark:text-zinc-400">Belum Dikonfigurasi</span>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Lingkungan (Environment)</p>
                <div class="mt-2">
                    @if ($profile?->midtrans_is_production)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                            <flux:icon.rocket-launch class="size-3.5" />
                            Production (Live / Uang Asli)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                            <flux:icon.beaker class="size-3.5" />
                            Sandbox (Simulasi / Uji Coba)
                        </span>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Fasilitas TPS3R Terkait</p>
                <p class="mt-2 text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $profile?->nama_tps3r ?? 'TPS3R Kota Madiun' }}</p>
                <p class="text-xs text-zinc-500">{{ $profile?->kelurahan ? 'Kel. ' . $profile->kelurahan : 'Kota Madiun' }}</p>
            </div>
        </div>

        {{-- Test Result Alert Inline --}}
        <div x-cloak x-show="testResult !== null" class="rounded-xl p-4 text-sm transition"
             :class="testResult?.success ? 'border border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200' : 'border border-rose-300 bg-rose-50 text-rose-900 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200'">
            <div class="flex items-start gap-2">
                <flux:icon.check-circle x-show="testResult?.success" class="size-5 shrink-0" />
                <flux:icon.exclamation-triangle x-show="!testResult?.success" class="size-5 shrink-0" />
                <div>
                    <p class="font-bold" x-text="testResult?.success ? 'Hasil Tes Koneksi: BERHASIL' : 'Hasil Tes Koneksi: GAGAL'"></p>
                    <p class="mt-1 text-xs" x-text="testResult?.message"></p>
                </div>
            </div>
        </div>

        <div x-cloak x-show="testErrorMessage !== ''" class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">
            <span x-text="testErrorMessage"></span>
        </div>

        {{-- Main Grid: Form Settings & Tutorial --}}
        <div class="grid gap-6 lg:grid-cols-12">
            
            {{-- Form Kredensial (Left / 5 cols) --}}
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-5">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Kredensial API Midtrans</h2>
                <p class="text-xs text-zinc-500">Masukkan Access Keys yang Anda dapatkan dari portal resmi Midtrans.</p>

                <form method="POST" action="{{ route('dashboard.tps3r.payment-settings.update') }}" class="mt-5 grid gap-4">
                    @csrf
                    @method('PUT')

                    <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                        Mode Environment
                        <select id="input_is_production" name="midtrans_is_production" {{ $isAdmin ? 'disabled' : '' }} class="rounded-lg border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <option value="0" {{ old('midtrans_is_production', $profile?->midtrans_is_production ? '1' : '0') === '0' ? 'selected' : '' }}>
                                Sandbox (Mode Uji Coba / Testing)
                            </option>
                            <option value="1" {{ old('midtrans_is_production', $profile?->midtrans_is_production ? '1' : '0') === '1' ? 'selected' : '' }}>
                                Production (Mode Live / Uang Nyata)
                            </option>
                        </select>
                        <span class="text-[11px] font-normal text-zinc-400">Pilih Sandbox saat masa uji coba/magang, pilih Production jika akun Midtrans telah diverifikasi legalitasnya.</span>
                    </label>

                    <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                        Merchant ID (Opsional)
                        <input
                            type="text"
                            name="midtrans_merchant_id"
                            value="{{ old('midtrans_merchant_id', $profile?->midtrans_merchant_id) }}"
                            placeholder="Contoh: G123456789"
                            {{ $isAdmin ? 'readonly' : '' }}
                            class="rounded-lg border-zinc-300 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-800"
                        />
                    </label>

                    <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                        Client Key (Kunci Publik)
                        <input
                            type="text"
                            name="midtrans_client_key"
                            value="{{ old('midtrans_client_key', $profile?->midtrans_client_key) }}"
                            placeholder="Contoh: SB-Mid-client-XXXXX atau Mid-client-XXXXX"
                            {{ $isAdmin ? 'readonly' : '' }}
                            class="rounded-lg border-zinc-300 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-800"
                        />
                    </label>

                    <label class="grid gap-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                        Server Key (Kunci Rahasia Backend)
                        <input
                            type="password"
                            id="input_server_key"
                            name="midtrans_server_key"
                            value="{{ old('midtrans_server_key') }}"
                            placeholder="{{ $profile?->midtrans_server_key ? '•••••••• Terenkripsi di Database (Ketik baru jika ingin mengubah)' : 'Contoh: SB-Mid-server-XXXXX' }}"
                            {{ $isAdmin ? 'readonly' : '' }}
                            class="rounded-lg border-zinc-300 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-800"
                        />
                        <span class="text-[11px] font-normal text-zinc-400">Server Key disimpan menggunakan enkripsi AES-256 untuk menjamin keamanan penuh.</span>
                    </label>

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/60">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                name="midtrans_is_active"
                                value="1"
                                {{ old('midtrans_is_active', $profile?->midtrans_is_active) ? 'checked' : '' }}
                                {{ $isAdmin ? 'disabled' : '' }}
                                class="mt-0.5 rounded border-zinc-300 text-teal-600 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800"
                            />
                            <div class="text-xs">
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">Aktifkan Pembayaran Midtrans</span>
                                <p class="text-zinc-500 dark:text-zinc-400">Jika dicentang, pembeli di mobile dapat memilih metode pembayaran Midtrans (QRIS & Virtual Account) untuk produk TPS3R ini.</p>
                            </div>
                        </label>
                    </div>

                    @if (! $isAdmin)
                        <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                            <button
                                type="submit"
                                class="inline-flex flex-1 items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800"
                            >
                                Simpan Kredensial Midtrans
                            </button>
                            <button
                                type="button"
                                @click="testMidtrans"
                                :disabled="testingConnection"
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-teal-700 bg-white px-3.5 py-2.5 text-xs font-semibold text-teal-700 shadow-sm transition hover:bg-teal-50 dark:border-teal-400 dark:bg-zinc-800 dark:text-teal-300 dark:hover:bg-zinc-700"
                            >
                                <span x-show="!testingConnection" class="inline-flex items-center gap-1.5"><flux:icon.beaker class="size-4" /> Uji Koneksi</span>
                                <span x-show="testingConnection" class="inline-flex items-center gap-1.5"><flux:icon.loading class="size-4" /> Menguji...</span>
                            </button>
                        </div>
                    @endif
                </form>
            </section>

            {{-- Tutorial & Panduan Terpadu (Right / 7 cols) --}}
            <section class="flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-7">
                <div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                        <flux:icon.book-open class="size-3.5" />
                        Tutorial Resmi
                    </span>
                    <h2 class="mt-2 text-lg font-bold text-zinc-900 dark:text-zinc-100">Panduan Menghubungkan Akun Midtrans TPS3R</h2>
                    <p class="text-xs text-zinc-500">Ikuti 5 langkah sederhana berikut untuk mendaftarkan dan mengaktifkan akun Midtrans Anda secara mandiri.</p>
                </div>

                {{-- Step-by-Step Interactive Cards --}}
                <div class="space-y-4">
                    
                    {{-- Step 1 --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <div class="flex items-start gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-700 font-mono text-xs font-bold text-white">1</span>
                            <div class="text-xs">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100">Daftar Akun Portal Midtrans</h3>
                                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                                    Buka portal resmi Midtrans dan buat akun baru menggunakan email resmi pengelola TPS3R / Bank Sampah:
                                </p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <a href="https://sandbox.midtrans.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg border border-amber-300 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-200">
                                        <flux:icon.arrow-top-right-on-square class="size-3.5" />
                                        Buka Midtrans Sandbox (Uji Coba)
                                    </a>
                                    <a href="https://dashboard.midtrans.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200">
                                        <flux:icon.arrow-top-right-on-square class="size-3.5" />
                                        Buka Midtrans Production (Live)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <div class="flex items-start gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-700 font-mono text-xs font-bold text-white">2</span>
                            <div class="text-xs">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100">Atur Rekening Bank untuk Pencairan (Disbursement)</h3>
                                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                                    Pada menu <strong>Settings &gt; Payouts</strong> atau <strong>Billing</strong> di dashboard Midtrans, masukkan data rekening bank (BCA, BRI, Mandiri, BNI, Jatim, dll.) milik TPS3R. Setiap ada transaksi sukses, dana akan otomatis disalurkan ke rekening bank ini.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <div class="flex items-start gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-700 font-mono text-xs font-bold text-white">3</span>
                            <div class="text-xs">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100">Salin Client Key & Server Key</h3>
                                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                                    Pada portal Midtrans, klik menu <strong>Settings</strong> di bilah kiri, lalu pilih <strong>Access Keys</strong>:
                                </p>
                                <ul class="mt-1 list-disc list-inside space-y-0.5 text-zinc-600 dark:text-zinc-400">
                                    <li>Salin nilai <strong>Client Key</strong> ke kolom form di sebelah kiri.</li>
                                    <li>Salin nilai <strong>Server Key</strong> ke kolom form di sebelah kiri.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <div class="flex items-start gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-700 font-mono text-xs font-bold text-white">4</span>
                            <div class="text-xs flex-1">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100">Atur Webhook Notification URL (PENTING)</h3>
                                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                                    Agar sistem otomatis menandai pesanan warga sebagai <strong>Lunas</strong> saat pembayaran selesai, pasang URL berikut di menu <strong>Settings &gt; Configuration &gt; Payment Notification URL</strong>:
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <input
                                        type="text"
                                        readonly
                                        value="{{ $webhookUrl }}"
                                        class="flex-1 rounded-lg border-zinc-300 bg-white font-mono text-[11px] text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                    />
                                    <button
                                        type="button"
                                        @click="copyWebhook"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-zinc-800 px-3 py-2 text-xs font-bold text-white shadow-sm hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600"
                                    >
                                        <span x-show="!copiedWebhook" class="inline-flex items-center gap-1"><flux:icon.clipboard class="size-3.5" /> Salin URL</span>
                                        <span x-show="copiedWebhook" class="inline-flex items-center gap-1 text-emerald-300"><flux:icon.check class="size-3.5" /> Tersalin</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 5 --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <div class="flex items-start gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-700 font-mono text-xs font-bold text-white">5</span>
                            <div class="text-xs">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100">Uji Coba Pembayaran Menggunakan Midtrans Simulator</h3>
                                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                                    Saat menggunakan mode Sandbox, Anda tidak perlu mengeluarkan uang asli! Gunakan simulator resmi Midtrans untuk simulasi QRIS & Virtual Account:
                                </p>
                                <div class="mt-2">
                                    <a
                                        href="https://simulator.sandbox.midtrans.com"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-teal-700 hover:underline dark:text-teal-400"
                                    >
                                        <flux:icon.globe-alt class="size-3.5" />
                                        Buka Midtrans Payment Simulator
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
