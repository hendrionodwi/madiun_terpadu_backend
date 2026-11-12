@props([
    'verification' => null,
    'numberLabel' => 'Nomor Dokumen / NIB',
    'noteLabel' => 'Catatan Admin DLH',
])

@php
    $status = $verification?->status ?? 'draft';
    [$statusLabel, $statusClass] = match ($status) {
        'approved' => ['TERVERIFIKASI DLH', 'civic-status-success'],
        'submitted' => ['MENUNGGU VALIDASI DLH', 'civic-status-warning'],
        'rejected' => ['PERLU PERBAIKAN', 'civic-status-danger'],
        default => ['DRAFT BELUM DIAJUKAN', 'civic-status-info'],
    };
@endphp

<div {{ $attributes->class('rounded-2xl border border-slate-200/85 bg-slate-50/75 p-5 dark:border-slate-800 dark:bg-slate-950/70') }}>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-3 dark:border-slate-800">
        <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">
            Status Validasi DLH
        </p>
        <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
    </div>

    <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
        <div>
            <dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                {{ $numberLabel }}
            </dt>
            <dd class="mt-1 font-mono text-sm font-bold text-slate-900 dark:text-slate-100">
                {{ $verification?->document_number ?: '-' }}
            </dd>
        </div>

        <div>
            <dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                Tanggal Pengajuan
            </dt>
            <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                {{ $verification?->submitted_at?->format('d M Y H:i') ?? 'Belum diajukan' }}
            </dd>
        </div>

        <div>
            <dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                Kelengkapan Berkas
            </dt>
            <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                @if ($verification?->document_path)
                    <span class="inline-flex items-center gap-1.5 text-civic-700 dark:text-civic-300">
                        <flux:icon.check-circle class="size-4 shrink-0" />
                        Berkas legalitas tersimpan aman
                    </span>
                @else
                    <span class="text-slate-500 dark:text-slate-400">Belum ada berkas diunggah</span>
                @endif
            </dd>
        </div>

        <div>
            <dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">
                {{ $noteLabel }}
            </dt>
            <dd class="mt-1 text-sm leading-5 text-slate-700 dark:text-slate-200">
                {{ $verification?->admin_note ?: 'Belum ada catatan dari verifier DLH.' }}
            </dd>
        </div>
    </dl>
</div>
