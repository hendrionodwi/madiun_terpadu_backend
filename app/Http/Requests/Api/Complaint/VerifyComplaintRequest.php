<?php

namespace App\Http\Requests\Api\Complaint;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyComplaintRequest extends FormRequest
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
            'status_verifikasi' => ['required', 'string', 'in:valid,tidak_valid,selesai_dibersihkan'],
            'catatan_petugas' => ['required', 'string', 'max:1000'],
            'rekomendasi_tindak_lanjut' => ['nullable', 'string', 'max:1000'],
            'foto_bukti' => ['nullable', 'string', 'max:255'],
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
            'status_verifikasi.required' => 'Status verifikasi lapangan wajib dipilih (valid, tidak_valid, selesai_dibersihkan).',
            'status_verifikasi.in' => 'Status verifikasi harus salah satu dari: valid, tidak_valid, selesai_dibersihkan.',
            'catatan_petugas.required' => 'Catatan hasil verifikasi lapangan wajib diisi.',
        ];
    }
}
