<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\EntityVerification;
use App\Models\EnvironmentalReport;
use App\Models\PickupSchedule;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProduct;

class CivicProfilePresenter
{
    /**
     * Build unified header, role badge/tag, real metrics, and tab metadata for Web profile screens.
     *
     * @return array{
     *     display_name: string,
     *     email: string,
     *     phone_number: ?string,
     *     role_label: string,
     *     role_value: string,
     *     initials: string,
     *     avatar_url: ?string,
     *     badges: list<array{label: string, tone: string}>,
     *     metrics: list<array{label: string, value: string, tone: string}>,
     *     tabs: list<array{key: string, label: string, route: string, icon: string, active: bool}>
     * }
     */
    public static function forUser(
        User $user,
        string $activeTab = 'profile',
        ?EntityVerification $verification = null,
    ): array {
        $user->loadMissing([
            'wargaProfile',
            'petugasLapanganProfile',
            'pengelolaTps3rProfile',
            'pemrakarsaProfile',
            'adminDlhProfile',
            'entityVerification',
        ]);

        $verification ??= $user->entityVerification;

        return [
            'display_name' => self::resolveDisplayName($user),
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role_label' => $user->role->label(),
            'role_value' => $user->role->value,
            'initials' => $user->initials(),
            'avatar_url' => $user->avatar_url,
            'badges' => self::badgesFor($user, $verification),
            'metrics' => self::metricsFor($user, $verification),
            'tabs' => self::tabsFor($user, $activeTab),
        ];
    }

    /**
     * @return list<array{key: string, label: string, route: string, icon: string, active: bool}>
     */
    public static function tabsFor(User $user, string $activeTab = 'profile'): array
    {
        $tabs = [
            [
                'key' => 'profile',
                'label' => 'Informasi Pribadi',
                'route' => route('profile.edit'),
                'icon' => 'user',
                'active' => $activeTab === 'profile',
            ],
        ];

        if ($user->isPengelolaTps3r()) {
            $tabs[] = [
                'key' => 'entity',
                'label' => 'Profil & Legalitas TPS3R',
                'route' => route('dashboard.tps3r.profile.edit'),
                'icon' => 'building-storefront',
                'active' => $activeTab === 'entity',
            ];
        } elseif ($user->isPemrakarsa()) {
            $tabs[] = [
                'key' => 'entity',
                'label' => 'Profil & Legalitas Usaha',
                'route' => route('dashboard.pemrakarsa.profile.edit'),
                'icon' => 'building-office-2',
                'active' => $activeTab === 'entity',
            ];
        }

        $tabs[] = [
            'key' => 'password',
            'label' => 'Kata Sandi',
            'route' => route('password.edit'),
            'icon' => 'lock-closed',
            'active' => $activeTab === 'password',
        ];

        $tabs[] = [
            'key' => 'security',
            'label' => 'Keamanan & 2FA',
            'route' => route('security.edit'),
            'icon' => 'shield-check',
            'active' => $activeTab === 'security',
        ];

        $tabs[] = [
            'key' => 'appearance',
            'label' => 'Tampilan',
            'route' => route('appearance.edit'),
            'icon' => 'paint-brush',
            'active' => $activeTab === 'appearance',
        ];

        return $tabs;
    }

    private static function resolveDisplayName(User $user): string
    {
        return match ($user->role) {
            UserRole::PengelolaTps3r => $user->pengelolaTps3rProfile?->nama_tps3r ?: $user->name,
            UserRole::Pemrakarsa => $user->pemrakarsaProfile?->nama_instansi ?: $user->name,
            default => $user->name,
        };
    }

    /**
     * @return list<array{label: string, tone: string}>
     */
    private static function badgesFor(User $user, ?EntityVerification $verification): array
    {
        $badges = [];

        if ($user->isPengelolaTps3r()) {
            $profile = $user->pengelolaTps3rProfile;
            $badges[] = [
                'label' => $profile?->kode_tps3r ?: 'TPS3R Terdaftar',
                'tone' => 'info',
            ];
            if ($profile?->kecamatan) {
                $badges[] = [
                    'label' => 'Kec. '.$profile->kecamatan,
                    'tone' => 'emerald',
                ];
            }
            $badges[] = self::verificationBadge($verification);
        } elseif ($user->isPemrakarsa()) {
            $profile = $user->pemrakarsaProfile;
            if ($profile?->jenis_instansi) {
                $badges[] = [
                    'label' => $profile->jenis_instansi,
                    'tone' => 'info',
                ];
            }
            if ($profile?->nib) {
                $badges[] = [
                    'label' => 'NIB: '.$profile->nib,
                    'tone' => 'emerald',
                ];
            }
            $badges[] = self::verificationBadge($verification);
        } elseif ($user->isAdminDlh()) {
            $profile = $user->adminDlhProfile;
            $badges[] = [
                'label' => $profile?->nip ? 'NIP: '.$profile->nip : 'Dinas Lingkungan Hidup',
                'tone' => 'info',
            ];
            $badges[] = [
                'label' => $profile?->jabatan ?: ($profile?->bidang ?: 'Administrator Kota'),
                'tone' => 'emerald',
            ];
        } elseif ($user->isPetugasLapangan()) {
            $profile = $user->petugasLapanganProfile;
            $badges[] = [
                'label' => $profile?->id_wilayah_rute ?: 'Armada Lapangan',
                'tone' => 'info',
            ];
            if ($profile?->plat_nomor) {
                $badges[] = [
                    'label' => $profile->plat_nomor,
                    'tone' => 'emerald',
                ];
            }
        } else {
            $profile = $user->wargaProfile;
            $area = collect([$profile?->kelurahan, $profile?->kecamatan])->filter()->implode(', ');
            $badges[] = [
                'label' => $area !== '' ? $area : 'Kota Madiun',
                'tone' => 'emerald',
            ];
        }

        return $badges;
    }

    /**
     * @return array{label: string, tone: string}
     */
    private static function verificationBadge(?EntityVerification $verification): array
    {
        return match ($verification?->status) {
            'approved' => ['label' => 'Terverifikasi DLH', 'tone' => 'emerald'],
            'submitted' => ['label' => 'Menunggu Validasi DLH', 'tone' => 'amber'],
            'rejected' => ['label' => 'Perlu Perbaikan Legalitas', 'tone' => 'danger'],
            default => ['label' => 'Draft Legalitas', 'tone' => 'neutral'],
        };
    }

    /**
     * @return list<array{label: string, value: string, tone: string}>
     */
    private static function metricsFor(User $user, ?EntityVerification $verification): array
    {
        if ($user->isPengelolaTps3r()) {
            $profile = $user->pengelolaTps3rProfile;
            $depositsCount = $profile !== null
                ? Tps3rWasteDeposit::query()->where('tps3r_profile_id', $profile->id)->count()
                : 0;
            $productsCount = WasteProduct::query()->where('seller_id', $user->id)->count();
            $capacity = $profile?->kapasitas_harian_kg !== null
                ? number_format((float) $profile->kapasitas_harian_kg, 0, ',', '.').' kg'
                : '0 kg';

            return [
                ['label' => 'SETORAN', 'value' => (string) $depositsCount, 'tone' => 'emerald'],
                ['label' => 'PRODUK', 'value' => (string) $productsCount, 'tone' => 'info'],
                ['label' => 'KAPASITAS/HR', 'value' => $capacity, 'tone' => 'amber'],
            ];
        }

        if ($user->isPemrakarsa()) {
            $totalReports = EnvironmentalReport::query()->where('pemrakarsa_id', $user->id)->count();
            $approvedReports = EnvironmentalReport::query()
                ->where('pemrakarsa_id', $user->id)
                ->where('status', 'disetujui')
                ->count();
            $legalStatus = match ($verification?->status) {
                'approved' => 'VALID',
                'submitted' => 'PROSES',
                'rejected' => 'REVISI',
                default => 'DRAFT',
            };

            return [
                ['label' => 'LAPORAN', 'value' => (string) $totalReports, 'tone' => 'info'],
                ['label' => 'DISETUJUI', 'value' => (string) $approvedReports, 'tone' => 'emerald'],
                ['label' => 'LEGALITAS', 'value' => $legalStatus, 'tone' => 'amber'],
            ];
        }

        if ($user->isAdminDlh()) {
            $openComplaints = Complaint::query()
                ->whereNotIn('status', ['selesai', 'ditolak'])
                ->count();
            $pendingEntities = EntityVerification::query()
                ->where('status', 'submitted')
                ->count();
            $pendingReports = EnvironmentalReport::query()
                ->whereIn('status', ['menunggu_verifikasi', 'ditugaskan', 'dalam_proses'])
                ->count();

            return [
                ['label' => 'ADUAN AKTIF', 'value' => (string) $openComplaints, 'tone' => 'amber'],
                ['label' => 'ENTITAS BARU', 'value' => (string) $pendingEntities, 'tone' => 'info'],
                ['label' => 'LAPORAN MASUK', 'value' => (string) $pendingReports, 'tone' => 'emerald'],
            ];
        }

        if ($user->isPetugasLapangan()) {
            $profile = $user->petugasLapanganProfile;
            $assignedComplaints = Complaint::query()->where('assigned_petugas_id', $user->id)->count();
            $assignedReports = EnvironmentalReport::query()->where('assigned_petugas_id', $user->id)->count();
            $status = strtoupper(str_replace('_', ' ', $profile?->status_tugas ?: 'AKTIF'));

            return [
                ['label' => 'ADUAN TUGAS', 'value' => (string) $assignedComplaints, 'tone' => 'amber'],
                ['label' => 'INSPEKSI', 'value' => (string) $assignedReports, 'tone' => 'info'],
                ['label' => 'STATUS', 'value' => $status, 'tone' => 'emerald'],
            ];
        }

        $points = (int) ($user->wargaProfile?->saldo_poin ?? 0);
        $schedulesCount = PickupSchedule::query()->where('warga_id', $user->id)->count();
        $complaintsCount = Complaint::query()->where('pelapor_id', $user->id)->count();

        return [
            ['label' => 'POIN', 'value' => number_format($points, 0, ',', '.'), 'tone' => 'emerald'],
            ['label' => 'JADWAL', 'value' => (string) $schedulesCount, 'tone' => 'info'],
            ['label' => 'ADUAN', 'value' => (string) $complaintsCount, 'tone' => 'amber'],
        ];
    }
}
