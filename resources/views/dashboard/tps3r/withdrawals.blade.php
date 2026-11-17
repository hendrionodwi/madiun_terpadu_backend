<x-layouts::app :title="__('Settlement Marketplace Poin')">
    @php
        $canSubmitSettlement = $availableBalance >= \App\Actions\CreateMarketplacePointSettlement::MINIMUM_SETTLEMENT_AMOUNT;
    @endphp
    <div class="civic-page mx-auto w-full max-w-6xl">
        <x-civic.subpage-header
            breadcrumbLabel="Dashboard TPS3R"
            breadcrumbRoute="dashboard.tps3r"
            current="Settlement Poin"
            eyebrow="Keuangan Marketplace"
            title="Settlement Penjualan dengan Poin"
            description="Ajukan pembayaran untuk pesanan poin yang sudah selesai. Transaksi Midtrans langsung masuk ke merchant TPS3R."
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

        <div class="civic-alert-info" role="note">
            <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
            <span>Saldo settlement berasal dari pesanan poin yang selesai dan belum diklaim. Anda dapat mengajukan sebagian atau seluruh saldo.</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(280px,1fr)_minmax(0,2fr)]">
            <section class="civic-hero h-fit !p-6">
                <div class="civic-hero-grid" aria-hidden="true"></div>
                <p class="text-xs font-bold uppercase tracking-wider text-civic-200">Saldo settlement tersedia</p>
                <p class="mt-2 font-display text-3xl font-extrabold text-white">Rp {{ number_format($availableBalance, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-civic-100">{{ $eligibleFulfillments->count() }} pesanan selesai · minimum Rp10.000</p>

                <form method="POST" action="{{ route('dashboard.tps3r.withdrawals.store') }}" class="relative z-10 mt-6 grid gap-3.5">
                    @csrf
                    <label class="grid gap-1.5 text-xs font-bold text-civic-100">
                        <span>Nominal pengajuan</span>
                        <div class="flex min-h-11 overflow-hidden rounded-xl border border-white/25 bg-white/10 focus-within:border-white focus-within:ring-2 focus-within:ring-white/30">
                            <span class="flex items-center border-e border-white/20 px-3.5 text-sm font-extrabold text-white">Rp</span>
                            <input
                                type="number"
                                name="amount"
                                value="{{ old('amount', (int) $availableBalance) }}"
                                min="{{ \App\Actions\CreateMarketplacePointSettlement::MINIMUM_SETTLEMENT_AMOUNT }}"
                                max="{{ (int) $availableBalance }}"
                                step="1"
                                inputmode="numeric"
                                required
                                class="min-w-0 flex-1 border-0 bg-transparent px-3.5 text-sm font-bold text-white placeholder:text-white/60 focus:outline-none focus:ring-0"
                            >
                        </div>
                        <span class="font-normal text-civic-100">Maksimal Rp {{ number_format($availableBalance, 0, ',', '.') }}</span>
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
                        <span>Bank / penyedia e-wallet</span>
                        <input
                            name="destination_provider"
                            value="{{ old('destination_provider') }}"
                            placeholder="Contoh: Bank Jatim atau DANA"
                            required
                            class="min-h-11 rounded-xl border border-white/25 bg-white/10 px-3.5 text-sm text-white placeholder:text-white/60 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-civic-100">
                        <span>Nama pemilik rekening/akun</span>
                        <input
                            name="destination_account_name"
                            value="{{ old('destination_account_name') }}"
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
                    <button type="submit" @disabled(! $canSubmitSettlement) class="mt-2 inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-extrabold text-civic-900 shadow-sm transition hover:bg-civic-50 disabled:cursor-not-allowed disabled:opacity-50">
                        Ajukan settlement poin
                    </button>
                </form>

                <p class="relative z-10 mt-4 text-xs leading-5 text-civic-100">Pembayaran Midtrans tidak masuk pengajuan ini karena settlement-nya diproses langsung ke akun merchant TPS3R.</p>
            </section>

            <section data-ajax-zone="tps3r-withdrawals" class="civic-card overflow-hidden">
                <div class="civic-section-header">
                    <div>
                        <h2 class="font-display text-base font-extrabold text-slate-950 dark:text-white">Riwayat settlement</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Settlement poin baru dan arsip pencairan toko lama.</p>
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
                                    $withdrawalStatus = \App\Enums\FundWithdrawalStatus::from($withdrawal->status);
                                    $wClass = match ($withdrawalStatus) {
                                        \App\Enums\FundWithdrawalStatus::Paid => 'civic-status-success',
                                        \App\Enums\FundWithdrawalStatus::Rejected, \App\Enums\FundWithdrawalStatus::Failed => 'civic-status-danger',
                                        \App\Enums\FundWithdrawalStatus::Approved, \App\Enums\FundWithdrawalStatus::Processing => 'civic-status-info',
                                        default => 'civic-status-warning',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-xs text-slate-600 dark:text-slate-300">{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                                    <td class="font-bold text-slate-950 dark:text-white">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                    <td class="capitalize text-slate-700 dark:text-slate-300">
                                        <p class="mb-1 text-xs font-bold text-civic-700 dark:text-civic-300">
                                            {{ $withdrawal->source === 'marketplace_points' ? $withdrawal->marketplace_settlement_items_count.' pesanan poin' : 'Arsip pencairan lama' }}
                                        </p>
                                        {{ str_replace('_', ' ', $withdrawal->destination_type) }}
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $withdrawal->destination_provider ?? '-' }}</p>
                                        <p class="mt-0.5 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $withdrawal->destination_account }}</p>
                                        @if ($withdrawal->destination_account_name)
                                            <p class="text-xs text-slate-500 dark:text-slate-400">a.n. {{ $withdrawal->destination_account_name }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $wClass }}">{{ $withdrawalStatus->label() }}</span>
                                        @if ($withdrawal->admin_note)
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $withdrawal->admin_note }}</p>
                                        @endif
                                        @if ($withdrawal->payout_reference)
                                            <p class="mt-1 font-mono text-xs text-slate-500 dark:text-slate-400">Ref: {{ $withdrawal->payout_reference }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada riwayat settlement.</td>
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
