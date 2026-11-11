<x-layouts::app :title="__('Pengaturan Pembayaran Midtrans TPS3R')">
    @php
        $fieldClass = 'min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-civic-500 focus:ring-2 focus:ring-civic-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-civic-900';
    @endphp

    <div
        x-data="{
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
        }"
        class="civic-page mx-auto w-full max-w-7xl"
    >
        <x-civic.subpage-header
            :breadcrumbLabel="$isAdmin ? 'Dashboard Admin' : 'Dashboard TPS3R'"
            :breadcrumbRoute="$isAdmin ? 'dashboard.admin' : 'dashboard.tps3r'"
            current="Pengaturan Midtrans"
            eyebrow="Pembayaran Online Marketplace"
            title="Gateway Pembayaran Midtrans Mandiri"
            description="Konfigurasi akun Midtrans milik TPS3R Anda agar uang pembayaran pesanan warga (QRIS, Transfer Bank, E-Wallet) langsung masuk ke rekening bank fasilitas Anda."
            icon="credit-card"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.tps3r.orders') }}" class="civic-button-secondary !min-h-10 !py-2 !text-xs">
                    &larr; Lihat Pesanan Masuk
                </a>
            </x-slot:actions>
        </x-civic.subpage-header>

        {{-- Alerts --}}
        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
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
            <div class="civic-card border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Mode Monitoring Admin DLH</span>
                        <p class="text-sm text-amber-900 dark:text-amber-200">Anda dapat melihat status konfigurasi Midtrans untuk fasilitas TPS3R di bawah ini:</p>
                    </div>
                    @php
                        $tps3rOptions = $allTps3rList->mapWithKeys(fn ($tpsItem) => [
                            (string) $tpsItem->user_id => $tpsItem->nama_tps3r . ' (' . ($tpsItem->user?->name ?? 'User') . ')',
                        ])->all();
                    @endphp
                    <form method="GET" action="{{ route('dashboard.tps3r.payment-settings') }}" data-ajax-form data-ajax-targets="#tps3rPaymentConfigPanel" class="flex w-full items-center gap-2 sm:w-72">
                        <x-public.select-filter
                            name="tps3r_user_id"
                            :value="(string) ($profile?->user_id ?? '')"
                            :options="$tps3rOptions"
                            placeholder="Pilih TPS3R"
                            heightClass="h-9 text-xs"
                            alignRight
                        />
                    </form>
                </div>
            </div>
        @endif

        <div id="tps3rPaymentConfigPanel" data-ajax-zone="tps3r-payment-settings" data-ajax-region="panel" class="flex flex-col gap-6">
            {{-- Status Overview Card --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="civic-stat-card">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status Pembayaran Online</p>
                    <div class="mt-2 flex items-center gap-2">
                        @if ($profile?->isMidtransConfigured())
                            <span class="relative flex h-3 w-3">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                            </span>
                            <span class="font-display text-base font-extrabold text-civic-700 dark:text-civic-300">Aktif & Siap Menerima Pembayaran</span>
                        @else
                            <span class="h-3 w-3 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                            <span class="text-base font-semibold text-slate-600 dark:text-slate-400">Belum Dikonfigurasi</span>
                        @endif
                    </div>
                </div>

                <div class="civic-stat-card">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Lingkungan (Environment)</p>
                    <div class="mt-2">
                        @if ($profile?->midtrans_is_production)
                            <span class="civic-status-success">
                                <flux:icon.rocket-launch class="size-3.5" />
                                Production (Live / Uang Asli)
                            </span>
                        @else
                            <span class="civic-status-warning">
                                <flux:icon.beaker class="size-3.5" />
                                Sandbox (Simulasi / Uji Coba)
                            </span>
                        @endif
                    </div>
                </div>

                <div class="civic-stat-card">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fasilitas TPS3R Terkait</p>
                    <p class="mt-2 font-display text-base font-extrabold text-slate-950 dark:text-white">{{ $profile?->nama_tps3r ?? 'TPS3R Kota Madiun' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $profile?->kelurahan ? 'Kel. ' . $profile->kelurahan : 'Kota Madiun' }}</p>
                </div>
            </div>

            {{-- Test Result Alert Inline --}}
            <div
                x-cloak
                x-show="testResult !== null"
                class="rounded-2xl p-4 text-sm transition"
                :class="testResult?.success ? 'border border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200' : 'border border-red-200 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200'"
            >
                <div class="flex items-start gap-2">
                    <flux:icon.check-circle x-show="testResult?.success" class="size-5 shrink-0" />
                    <flux:icon.exclamation-triangle x-show="!testResult?.success" class="size-5 shrink-0" />
                    <div>
                        <p class="font-bold" x-text="testResult?.success ? 'Hasil Tes Koneksi: BERHASIL' : 'Hasil Tes Koneksi: GAGAL'"></p>
                        <p class="mt-1 text-xs" x-text="testResult?.message"></p>
                    </div>
                </div>
            </div>

            <div x-cloak x-show="testErrorMessage !== ''" class="civic-alert-danger">
                <span x-text="testErrorMessage"></span>
            </div>

            {{-- Main Grid: Form Settings & Tutorial --}}
            <div class="grid gap-6 lg:grid-cols-12">
                {{-- Form Kredensial (Left / 5 cols) --}}
                <section class="civic-card p-5 sm:p-6 lg:col-span-5">
                    <h2 class="font-display text-lg font-extrabold text-slate-950 dark:text-white">Kredensial API Midtrans</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Masukkan Access Keys yang Anda dapatkan dari portal resmi Midtrans.</p>

                    <form method="POST" action="{{ route('dashboard.tps3r.payment-settings.update') }}" class="mt-5 grid gap-4">
                        @csrf
                        @method('PUT')

                        <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <span>Mode Environment</span>
                            <select id="input_is_production" name="midtrans_is_production" {{ $isAdmin ? 'disabled' : '' }} class="{{ $fieldClass }}">
                                <option value="0" {{ old('midtrans_is_production', $profile?->midtrans_is_production ? '1' : '0') === '0' ? 'selected' : '' }}>
                                    Sandbox (Mode Uji Coba / Testing)
                                </option>
                                <option value="1" {{ old('midtrans_is_production', $profile?->midtrans_is_production ? '1' : '0') === '1' ? 'selected' : '' }}>
                                    Production (Mode Live / Uang Nyata)
                                </option>
                            </select>
                            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">Pilih Sandbox saat masa uji coba/magang, pilih Production jika akun Midtrans telah diverifikasi legalitasnya.</span>
                        </label>

                        <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <span>Merchant ID (Opsional)</span>
                            <input
                                type="text"
                                name="midtrans_merchant_id"
                                value="{{ old('midtrans_merchant_id', $profile?->midtrans_merchant_id) }}"
                                placeholder="Contoh: G123456789"
                                {{ $isAdmin ? 'readonly' : '' }}
                                class="{{ $fieldClass }} font-mono"
                            />
                        </label>

                        <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <span>Client Key (Kunci Publik)</span>
                            <input
                                type="text"
                                name="midtrans_client_key"
                                value="{{ old('midtrans_client_key', $profile?->midtrans_client_key) }}"
                                placeholder="Contoh: SB-Mid-client-XXXXX atau Mid-client-XXXXX"
                                {{ $isAdmin ? 'readonly' : '' }}
                                class="{{ $fieldClass }} font-mono"
                            />
                        </label>

                        <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <span>Server Key (Kunci Rahasia Backend)</span>
                            <input
                                type="password"
                                id="input_server_key"
                                name="midtrans_server_key"
                                value="{{ old('midtrans_server_key') }}"
                                placeholder="{{ $profile?->midtrans_server_key ? '•••••••• Terenkripsi di Database (Ketik baru jika ingin mengubah)' : 'Contoh: SB-Mid-server-XXXXX' }}"
                                {{ $isAdmin ? 'readonly' : '' }}
                                class="{{ $fieldClass }} font-mono"
                            />
                            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">Server Key disimpan menggunakan enkripsi AES-256 untuk menjamin keamanan penuh.</span>
                        </label>

                        <label class="grid gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <span>Signature Secret Key (Kunci Verifikasi Webhook)</span>
                            <input
                                type="password"
                                name="midtrans_signature_secret"
                                value="{{ old('midtrans_signature_secret') }}"
                                placeholder="{{ $profile?->midtrans_signature_secret ? '•••••••• Terenkripsi di Database (Ketik baru jika ingin mengubah)' : 'Salin dari Settings > Access Keys' }}"
                                {{ $isAdmin ? 'readonly' : '' }}
                                class="{{ $fieldClass }} font-mono"
                                autocomplete="new-password"
                            />
                            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">Digunakan untuk memastikan webhook benar-benar dikirim oleh Midtrans. Akun lama dapat membiarkannya kosong.</span>
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3.5 dark:border-slate-800 dark:bg-slate-800/50">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="midtrans_is_active"
                                    value="1"
                                    {{ old('midtrans_is_active', $profile?->midtrans_is_active) ? 'checked' : '' }}
                                    {{ $isAdmin ? 'disabled' : '' }}
                                    class="mt-0.5 rounded border-slate-300 text-civic-700 focus:ring-civic-500 dark:border-slate-700 dark:bg-slate-800"
                                />
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-slate-100">Aktifkan Pembayaran Midtrans</span>
                                    <p class="mt-0.5 text-slate-500 dark:text-slate-400">Jika dicentang, pembeli di mobile dapat memilih metode pembayaran Midtrans (QRIS & Virtual Account) untuk produk TPS3R ini.</p>
                                </div>
                            </label>
                        </div>

                        @if (! $isAdmin)
                            <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                                <button type="submit" class="civic-button-primary flex-1 !py-2.5 !text-xs">
                                    Simpan Kredensial Midtrans
                                </button>
                                <button
                                    type="button"
                                    @click="testMidtrans"
                                    :disabled="testingConnection"
                                    class="civic-button-secondary !py-2.5 !text-xs"
                                >
                                    <span x-show="!testingConnection" class="inline-flex items-center gap-1.5"><flux:icon.beaker class="size-4" /> Uji Koneksi</span>
                                    <span x-show="testingConnection" class="inline-flex items-center gap-1.5"><flux:icon.loading class="size-4" /> Menguji...</span>
                                </button>
                            </div>
                        @endif
                    </form>
                </section>

                {{-- Tutorial & Panduan Terpadu (Right / 7 cols) --}}
                <section class="civic-card flex flex-col gap-5 p-5 sm:p-6 lg:col-span-7">
                    <div>
                        <span class="civic-status-info">
                            <flux:icon.book-open class="size-3.5" />
                            Tutorial Resmi
                        </span>
                        <h2 class="mt-2 font-display text-lg font-extrabold text-slate-950 dark:text-white">Panduan Menghubungkan Akun Midtrans TPS3R</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ikuti 5 langkah sederhana berikut untuk mendaftarkan dan mengaktifkan akun Midtrans Anda secara mandiri.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-start gap-3">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-civic-700 font-mono text-xs font-bold text-white">1</span>
                                <div class="text-xs">
                                    <h3 class="font-bold text-slate-950 dark:text-white">Daftar Akun Portal Midtrans</h3>
                                    <p class="mt-1 text-slate-600 dark:text-slate-300">
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

                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-start gap-3">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-civic-700 font-mono text-xs font-bold text-white">2</span>
                                <div class="text-xs">
                                    <h3 class="font-bold text-slate-950 dark:text-white">Atur Rekening Bank untuk Pencairan (Disbursement)</h3>
                                    <p class="mt-1 text-slate-600 dark:text-slate-300">
                                        Pada menu <strong>Settings &gt; Payouts</strong> atau <strong>Billing</strong> di dashboard Midtrans, masukkan data rekening bank (BCA, BRI, Mandiri, BNI, Jatim, dll.) milik TPS3R. Setiap ada transaksi sukses, dana akan otomatis disalurkan ke rekening bank ini.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-start gap-3">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-civic-700 font-mono text-xs font-bold text-white">3</span>
                                <div class="text-xs">
                                    <h3 class="font-bold text-slate-950 dark:text-white">Salin Client Key, Server Key & Signature Secret</h3>
                                    <p class="mt-1 text-slate-600 dark:text-slate-300">
                                        Pada portal Midtrans, klik menu <strong>Settings</strong> di bilah kiri, lalu pilih <strong>Access Keys</strong>:
                                    </p>
                                    <ul class="mt-1 list-inside list-disc space-y-0.5 text-slate-600 dark:text-slate-400">
                                        <li>Salin nilai <strong>Client Key</strong> ke kolom form di sebelah kiri.</li>
                                        <li>Salin nilai <strong>Server Key</strong> ke kolom form di sebelah kiri.</li>
                                        <li>Untuk dashboard Midtrans baru, salin juga <strong>Signature Secret Key</strong> untuk verifikasi webhook.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-start gap-3">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-civic-700 font-mono text-xs font-bold text-white">4</span>
                                <div class="flex-1 text-xs">
                                    <h3 class="font-bold text-slate-950 dark:text-white">Atur Webhook Notification URL (PENTING)</h3>
                                    <p class="mt-1 text-slate-600 dark:text-slate-300">
                                        Agar sistem otomatis menandai pesanan warga sebagai <strong>Lunas</strong> saat pembayaran selesai, pasang URL berikut di menu <strong>Settings &gt; Configuration &gt; Payment Notification URL</strong>:
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $webhookUrl }}"
                                            class="min-h-10 flex-1 rounded-xl border border-slate-200 bg-white px-3 font-mono text-xs text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                        />
                                        <button
                                            type="button"
                                            @click="copyWebhook"
                                            class="civic-button-primary !min-h-10 shrink-0 !px-3 !py-2 !text-xs"
                                        >
                                            <span x-show="!copiedWebhook" class="inline-flex items-center gap-1"><flux:icon.clipboard class="size-3.5" /> Salin URL</span>
                                            <span x-show="copiedWebhook" class="inline-flex items-center gap-1 text-emerald-200"><flux:icon.check class="size-3.5" /> Tersalin</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-start gap-3">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-civic-700 font-mono text-xs font-bold text-white">5</span>
                                <div class="text-xs">
                                    <h3 class="font-bold text-slate-950 dark:text-white">Uji Coba Pembayaran Menggunakan Midtrans Simulator</h3>
                                    <p class="mt-1 text-slate-600 dark:text-slate-300">
                                        Saat menggunakan mode Sandbox, Anda tidak perlu mengeluarkan uang asli! Gunakan simulator resmi Midtrans untuk simulasi QRIS & Virtual Account:
                                    </p>
                                    <div class="mt-2">
                                        <a href="https://simulator.sandbox.midtrans.com" target="_blank" rel="noopener noreferrer" class="civic-link">
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
    </div>
</x-layouts::app>
