<?php

namespace Database\Factories;

use App\Models\PointLedger;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointLedger>
 */
class PointLedgerFactory extends Factory
{
    protected $model = PointLedger::class;

    public function definition(): array
    {
        $tipe = fake()->randomElement(['kredit', 'debit']);
        $kategori = $tipe === 'kredit'
            ? fake()->randomElement(['setor_sampah', 'refund_marketplace', 'refund_penukaran_poin', 'refund_retribusi'])
            : fake()->randomElement(['belanja_marketplace', 'penukaran_poin', 'bayar_retribusi']);
        $jumlah = fake()->numberBetween(10, 500);

        return [
            'warga_id' => User::factory()->warga(),
            'tipe' => $tipe,
            'kategori' => $kategori,
            'jumlah_poin' => $jumlah,
            'saldo_sebelumnya' => 1000,
            'saldo_setelahnya' => $tipe === 'kredit' ? 1000 + $jumlah : 1000 - $jumlah,
            'referensi_tipe' => null,
            'referensi_id' => null,
            'deskripsi' => fake()->sentence(),
        ];
    }
}
