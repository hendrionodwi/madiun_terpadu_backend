<x-layouts::app :title="__('Pencairan Pendapatan')">
    <div class="civic-page mx-auto w-full max-w-6xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Pencairan Dana"
            eyebrow="Keuangan Marketplace"
            title="Pencairan Pendapatan Toko"
            description="Pendapatan berasal dari pesanan seller yang fulfillment-nya sudah selesai."
            icon="banknotes"
        />

        @if (session('status'))
            <div class="civic-alert-success" role="status">
                <flux:icon.check-circle class="mt-0.5 size-5 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="civic-alert-danger" role="alert">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <section class="civic-hero h-fit !p-6">
                <div class="civic-hero-grid" aria-hidden="true"></div>
                <p class="text-xs font-bold uppercase tracking-wider text-civic-200">Saldo toko tersedia</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-white">Rp {{ number_format($availableBalance, 0, ',', '.') }}</p>

                <form method="POST" action="{{ route('dashboard.tps3r.withdrawals.store') }}" class="relative z-10 mt-6 grid gap-3.5">
                    @csrf
                    <label class="grid gap-1.5 text-xs font-bold text-civic-100">
                        <span>Nominal</span>
                        <input
                            type="number"
                            min="10000"
                            max="{{ floor($availableBalance) }}"
                            name="amount"
                            required
                            class="min-h-11 rounded-xl border border-white/25 bg-white/10 px-3.5 text-sm text-white placeholder:text-white/60 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-civic-100">
                        <span>Tujuan</span>
                        <select
                            name="destination_type"
                            class="min-h-11 rounded-xl border border-white/25 bg-civic-950/80 px-3.5 text-sm text-white focus:border-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                            <option value="bank_transfer">Transfer bank</option>
                            <option value="e_wallet">E-Wallet</option>
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-civic-100">
                        <span>Nomor rekening/akun</span>
                        <input
                            name="destination_account"
                            required
                            class="min-h-11 rounded-xl border border-white/25 bg-white/10 px-3.5 text-sm text-white placeholder:text-white/60 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-civic-100">
                        <span>Catatan</span>
                        <input
                            name="requester_note"
                            class="min-h-11 rounded-xl border border-white/25 bg-white/10 px-3.5 text-sm text-white placeholder:text-white/60 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                    </label>
                    <button type="submit" class="mt-2 inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-extrabold text-civic-900 shadow-sm transition hover:bg-civic-50">
                        Ajukan pencairan
                    </button>
                </form>
            </section>

            <section data-ajax-zone="tps3r-withdrawals" class="civic-card overflow-hidden">
                <div class="civic-section-header">
                    <div>
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Riwayat permintaan</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Status pengajuan pencairan pendapatan toko TPS3R.</p>
                    </div>
                </div>

                <div class="civic-table-wrap rounded-none border-0">
                    <table class="civic-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Tujuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($withdrawals as $withdrawal)
                                @php
                                    $wClass = $withdrawal->status === 'approved' ? 'civic-status-success' : ($withdrawal->status === 'rejected' ? 'civic-status-danger' : 'civic-status-warning');
                                @endphp
                                <tr>
                                    <td class="text-xs text-slate-600 dark:text-slate-300">{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                                    <td class="font-bold text-slate-950 dark:text-white">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                    <td class="capitalize text-slate-700 dark:text-slate-300">
                                        {{ str_replace('_', ' ', $withdrawal->destination_type) }}
                                        <p class="mt-0.5 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $withdrawal->destination_account }}</p>
                                    </td>
                                    <td>
                                        <span class="{{ $wClass }} uppercase">{{ $withdrawal->status }}</span>
                                        @if ($withdrawal->admin_note)
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $withdrawal->admin_note }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada permintaan pencairan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($withdrawals->hasPages())
                    <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts::app>
