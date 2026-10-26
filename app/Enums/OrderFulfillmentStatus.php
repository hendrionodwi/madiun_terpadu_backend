<?php

namespace App\Enums;

enum OrderFulfillmentStatus: string
{
    case Menunggu = 'menunggu';
    case Diproses = 'diproses';
    case SiapDikirim = 'siap_dikirim';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu diproses',
            self::Diproses => 'Sedang diproses',
            self::SiapDikirim => 'Siap dikirim',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /** @return array<int, self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Menunggu => [self::Diproses, self::Dibatalkan],
            self::Diproses => [self::SiapDikirim, self::Dibatalkan],
            self::SiapDikirim => [self::Selesai],
            self::Selesai, self::Dibatalkan => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return $this === $target || in_array($target, $this->allowedTransitions(), true);
    }

    public function timestampColumn(): ?string
    {
        return match ($this) {
            self::Menunggu => null,
            self::Diproses => 'processed_at',
            self::SiapDikirim => 'ready_at',
            self::Selesai => 'completed_at',
            self::Dibatalkan => 'cancelled_at',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Selesai, self::Dibatalkan], true);
    }
}
