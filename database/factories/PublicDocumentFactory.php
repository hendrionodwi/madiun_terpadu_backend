<?php

namespace Database\Factories;

use App\Models\PublicDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PublicDocument>
 */
class PublicDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'created_by' => User::factory()->adminDlh(),
            'judul' => $title,
            'slug' => Str::slug($title).'-'.strtolower(Str::random(5)),
            'kategori' => fake()->randomElement(['panduan', 'regulasi', 'peta', 'laporan']),
            'deskripsi' => fake()->sentence(12),
            'file_path' => 'documents/'.Str::slug($title).'.pdf',
            'file_format' => 'PDF',
            'file_size_bytes' => fake()->numberBetween(350000, 4200000),
            'tanggal_rilis' => now()->subDays(fake()->numberBetween(1, 90))->toDateString(),
            'hak_akses_label' => 'Akses Bebas Publik',
            'status' => 'published',
            'download_count' => fake()->numberBetween(0, 150),
        ];
    }
}
