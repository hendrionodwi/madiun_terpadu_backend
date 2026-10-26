@props(['user'])

<div {{ $attributes->class('min-w-0') }}>
    @if ($user->wargaProfile)
        <p class="truncate font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">NIK: {{ $user->wargaProfile->nik }}</p>
        <p class="mt-1 truncate text-[0.68rem] text-slate-500 dark:text-slate-400">{{ $user->wargaProfile->kelurahan }}, {{ $user->wargaProfile->kecamatan }}</p>
    @elseif ($user->petugasLapanganProfile)
        <p class="truncate font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">Rute: {{ $user->petugasLapanganProfile->id_wilayah_rute ?: 'Belum ditetapkan' }}</p>
        <p class="mt-1 truncate text-[0.68rem] text-slate-500 dark:text-slate-400">{{ $user->petugasLapanganProfile->jenis_kendaraan ?: 'Kendaraan belum diisi' }} ({{ $user->petugasLapanganProfile->plat_nomor ?: '-' }})</p>
    @elseif ($user->pengelolaTps3rProfile)
        <p class="truncate text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $user->pengelolaTps3rProfile->nama_tps3r }}</p>
        <p class="mt-1 truncate text-[0.68rem] text-slate-500 dark:text-slate-400">Kapasitas {{ number_format($user->pengelolaTps3rProfile->kapasitas_harian_kg) }} kg/hari</p>
    @elseif ($user->pemrakarsaProfile)
        <p class="truncate text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $user->pemrakarsaProfile->nama_instansi }}</p>
        <p class="mt-1 truncate text-[0.68rem] text-slate-500 dark:text-slate-400">NIB: {{ $user->pemrakarsaProfile->nib }}</p>
    @elseif ($user->adminDlhProfile)
        <p class="truncate font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">NIP: {{ $user->adminDlhProfile->nip }}</p>
        <p class="mt-1 truncate text-[0.68rem] text-slate-500 dark:text-slate-400">{{ $user->adminDlhProfile->jabatan }}</p>
    @else
        <p class="text-xs italic text-slate-400">Profil role belum dilengkapi</p>
    @endif
</div>
