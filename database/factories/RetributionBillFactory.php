<?php

namespace Database\Factories;

use App\Models\RetributionBill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RetributionBill>
 */
class RetributionBillFactory extends Factory
{
    protected $model = RetributionBill::class;

    public function definition(): array
    {
        $year = 2026;
        $month = 10;
        $nominal = 10000.0;

        return [
            'nomor_tagihan' => 'RET-202610-'.fake()->unique()->numerify('####'),
            'warga_id' => User::factory()->warga(),
            'periode_bulan' => $month,
            'periode_tahun' => $year,
            'nominal_tagihan' => $nominal,
            'poin_digunakan' => 0,
            'nominal_potongan_poin' => 0,
            'nominal_dibayar' => $nominal,
            'metode_pembayaran' => 'belum_dipilih',
            'status' => 'belum_dibayar',
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'paid_at' => null,
            'midtrans_order_id' => null,
            'snap_token' => null,
            'snap_redirect_url' => null,
            'payment_gateway_response' => null,
            'catatan' => 'Iuran retribusi sampah bulanan Kota Madiun',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'lunas',
            'metode_pembayaran' => 'poin_sampah',
            'poin_digunakan' => 1000,
            'nominal_potongan_poin' => 10000.0,
            'nominal_dibayar' => 0,
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => 'belum_dibayar',
            'jatuh_tempo' => now()->subDays(5)->toDateString(),
        ]);
    }
}
