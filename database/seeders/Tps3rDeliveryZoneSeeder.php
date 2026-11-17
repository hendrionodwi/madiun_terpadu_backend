<?php

namespace Database\Seeders;

use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use Illuminate\Database\Seeder;

class Tps3rDeliveryZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $approverId = User::query()
            ->where('role', 'admin_dlh')
            ->value('id');

        PengelolaTps3rProfile::query()->each(function (PengelolaTps3rProfile $profile) use ($approverId): void {
            $configuration = $this->configurationFor($profile);

            if ($profile->rajaongkir_origin_id === null && $configuration['origin_id'] !== null) {
                $profile->update(['rajaongkir_origin_id' => $configuration['origin_id']]);
            }

            foreach ($configuration['zones'] as $zone) {
                Tps3rDeliveryZone::query()->updateOrCreate(
                    [
                        'pengelola_tps3r_profile_id' => $profile->id,
                        'kelurahan' => $zone['kelurahan'],
                        'kecamatan' => $zone['kecamatan'],
                    ],
                    [
                        'delivery_fee' => $zone['delivery_fee'],
                        'status' => $approverId === null
                            ? Tps3rDeliveryZone::STATUS_PENDING
                            : Tps3rDeliveryZone::STATUS_APPROVED,
                        'is_active' => true,
                        'approved_by' => $approverId,
                        'approved_at' => $approverId === null ? null : now(),
                        'rejection_reason' => null,
                    ],
                );
            }
        });
    }

    /**
     * @return array{
     *     origin_id: int|null,
     *     zones: list<array{kelurahan: string, kecamatan: string, delivery_fee: int}>
     * }
     */
    private function configurationFor(PengelolaTps3rProfile $profile): array
    {
        return match (mb_strtolower((string) $profile->kecamatan)) {
            'kartoharjo' => [
                'origin_id' => 39876,
                'zones' => [
                    ['kelurahan' => 'Pandean', 'kecamatan' => 'Taman', 'delivery_fee' => 7000],
                    ['kelurahan' => 'Oro-Oro Ombo', 'kecamatan' => 'Kartoharjo', 'delivery_fee' => 5000],
                ],
            ],
            'manguharjo' => [
                'origin_id' => 39889,
                'zones' => [
                    ['kelurahan' => 'Pangongangan', 'kecamatan' => 'Manguharjo', 'delivery_fee' => 5000],
                    ['kelurahan' => 'Nambangan Lor', 'kecamatan' => 'Manguharjo', 'delivery_fee' => 7000],
                ],
            ],
            default => [
                'origin_id' => null,
                'zones' => $profile->kelurahan !== null && $profile->kecamatan !== null
                    ? [[
                        'kelurahan' => $profile->kelurahan,
                        'kecamatan' => $profile->kecamatan,
                        'delivery_fee' => 7000,
                    ]]
                    : [],
            ],
        };
    }
}
