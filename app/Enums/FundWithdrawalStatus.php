<?php

namespace App\Enums;

enum FundWithdrawalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Processing = 'processing';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu verifikasi',
            self::Approved => 'Disetujui, menunggu pembayaran',
            self::Processing => 'Sedang dibayarkan',
            self::Paid => 'Sudah dibayar',
            self::Rejected => 'Ditolak',
            self::Failed => 'Pembayaran gagal',
        };
    }

    /** @return array<int, self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected],
            self::Approved => [self::Processing, self::Failed],
            self::Processing => [self::Paid, self::Failed],
            self::Paid, self::Rejected, self::Failed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Rejected, self::Failed], true);
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
