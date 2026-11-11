<?php

namespace Database\Factories;

use App\Models\EducationArticle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EducationArticle>
 */
class EducationArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'created_by' => User::factory()->adminDlh(),
            'judul' => $title,
            'slug' => Str::slug($title).'-'.strtolower(Str::random(5)),
            'kategori' => fake()->randomElement(['organik', 'plastik_kertas', 'b3_rumah_tangga', 'ekonomi_sirkular', 'kebijakan']),
            'ringkasan' => fake()->paragraph(),
            'isi' => implode("\n\n", fake()->paragraphs(4)),
            'penulis_nama' => 'Tim Penyuluh DLH Kota Madiun',
            'penulis_jabatan' => 'Bidang Pengelolaan Sampah',
            'waktu_baca_menit' => fake()->numberBetween(3, 8),
            'cover_image' => null,
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
