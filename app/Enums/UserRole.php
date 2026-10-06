<?php

namespace App\Enums;

enum UserRole: string
{
    case Warga = 'warga';
    case PetugasLapangan = 'petugas_lapangan';
    case PengelolaTps3r = 'pengelola_tps3r';
    case Pemrakarsa = 'pemrakarsa';
    case AdminDlh = 'admin_dlh';

    /**
     * Get all role string values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Warga => 'Warga',
            self::PetugasLapangan => 'Petugas Lapangan',
            self::PengelolaTps3r => 'Pengelola TPS3R',
            self::Pemrakarsa => 'Pemrakarsa',
            self::AdminDlh => 'Admin DLH',
        };
    }
}
