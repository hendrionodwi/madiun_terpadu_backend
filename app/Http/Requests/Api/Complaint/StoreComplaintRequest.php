<?php

namespace App\Http\Requests\Api\Complaint;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori_masalah' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'lokasi' => ['required', 'string', 'max:500'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_anonim' => ['nullable', 'boolean'],
            'foto' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->hasFile('foto')) {
                        $file = $this->file('foto');
                        if (! $file || ! $file->isValid()) {
                            $fail('File foto bukti tidak valid.');

                            return;
                        }
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
                            $fail('Foto bukti harus berupa gambar (JPG, JPEG, PNG, atau WEBP).');

                            return;
                        }
                        if ($file->getSize() > 5120 * 1024) {
                            $fail('Ukuran foto bukti maksimal 5 MB.');

                            return;
                        }
                    } elseif (! is_string($value) || trim($value) === '') {
                        $fail('Foto bukti tumpukan sampah wajib dilampirkan.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul pengaduan wajib diisi.',
            'deskripsi.required' => 'Deskripsi pengaduan wajib diisi.',
            'lokasi.required' => 'Lokasi kejadian / tumpukan sampah wajib diisi.',
            'foto.required' => 'Foto bukti tumpukan sampah wajib dilampirkan sebagai bukti pertanggungjawaban.',
        ];
    }
}
