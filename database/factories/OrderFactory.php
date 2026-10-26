<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_pesanan' => 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(6)),
            'buyer_id' => User::factory()->warga(),
            'total_harga' => 50000.00,
            'metode_pembayaran' => 'transfer_bank',
            'status' => 'pending',
            'alamat_pengiriman' => fake()->address(),
            'catatan' => 'Pesanan produk daur ulang',
        ];
    }
}
