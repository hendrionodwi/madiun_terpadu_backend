<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WasteProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WasteProduct>
 */
class WasteProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => User::factory()->pengelolaTps3r(),
            'nama_produk' => fake()->randomElement(['Pupuk Kompos Organik Super', 'Tas Anyaman Plastik Daur Ulang', 'Pakan Maggot BSF Kering', 'Pot Tanaman Ecobrick']),
            'deskripsi' => fake()->paragraph(),
            'kategori' => fake()->randomElement(['pupuk_kompos', 'kerajinan_plastik', 'maggot_pakan', 'ecobrick']),
            'harga' => fake()->numberBetween(10000, 75000),
            'stok' => fake()->numberBetween(10, 100),
            'foto' => null,
            'is_available' => true,
        ];
    }
}
