@props(['role'])

@php
    $tone = match ($role->value) {
        'admin_dlh' => 'bg-purple-100 text-purple-800 ring-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:ring-purple-900',
        'petugas_lapangan' => 'bg-blue-100 text-blue-800 ring-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:ring-blue-900',
        'pengelola_tps3r' => 'bg-cyan-100 text-cyan-800 ring-cyan-200 dark:bg-cyan-950/60 dark:text-cyan-300 dark:ring-cyan-900',
        'pemrakarsa' => 'bg-indigo-100 text-indigo-800 ring-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:ring-indigo-900',
        default => 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:ring-emerald-900',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2.5 py-1 text-[0.62rem] font-extrabold uppercase tracking-[0.08em] ring-1 ring-inset {$tone}") }}>
    {{ $role->label() }}
</span>
