<?php

namespace App\Http\Requests\Cms;

use App\Models\EducationArticle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class SaveEducationArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdminDlh();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'judul' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'kategori' => [$isCreate ? 'required' : 'sometimes', 'string', 'in:organik,plastik_kertas,b3_rumah_tangga,ekonomi_sirkular,kebijakan'],
            'ringkasan' => ['nullable', 'string', 'max:1000'],
            'isi' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'penulis_nama' => ['nullable', 'string', 'max:150'],
            'penulis_jabatan' => ['nullable', 'string', 'max:150'],
            'waktu_baca_menit' => ['nullable', 'integer', 'min:1', 'max:60'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['nullable', 'string', 'in:draft,scheduled,published,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Require a publication timestamp when an article is set to scheduled.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('published_at')) {
                    return;
                }

                $existing = $this->route('educationArticle');
                $existingModel = $existing instanceof EducationArticle ? $existing : null;

                $status = $this->filled('status')
                    ? (string) $this->input('status')
                    : ($existingModel?->status ?? 'published');

                $publishedAt = $this->exists('published_at')
                    ? ($this->filled('published_at') ? Carbon::parse((string) $this->input('published_at')) : null)
                    : $existingModel?->published_at;

                if ($status === 'scheduled' && $publishedAt === null) {
                    $validator->errors()->add('published_at', 'Tanggal publikasi wajib diisi jika status artikel terjadwal.');
                }
            },
        ];
    }
}
