<?php

namespace App\Http\Requests\Api\Tps3r;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTps3rDepositRequest extends FormRequest
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
            'petugas_id' => ['nullable', 'integer', 'exists:users,id'],
            'tps3r_profile_id' => ['nullable', 'integer', 'exists:pengelola_tps3r_profiles,id'],
            'route_code' => ['nullable', 'string', 'max:50'],
            'weighing_ids' => ['nullable', 'array'],
            'weighing_ids.*' => ['integer', 'exists:waste_weighings,id'],
            'sumber_sampah' => ['required', 'string', 'in:armada_kelurahan,warga_mandiri,pemrakarsa_komersial'],
            'jenis_sampah' => ['required', 'string', 'in:organik,anorganik_daur_ulang,residu'],
            'berat_kg' => ['required', 'numeric', 'min:0.1', 'max:99999.99'],
            'berat_organik_pilah_kg' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'berat_anorganik_pilah_kg' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'berat_residu_pilah_kg' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'tanggal_setor' => ['required', 'date_format:Y-m-d'],
            'catatan' => ['nullable', 'string', 'max:500'],
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
            'sumber_sampah.required' => 'Sumber sampah wajib dipilih (armada_kelurahan, warga_mandiri, pemrakarsa_komersial).',
            'jenis_sampah.required' => 'Jenis sampah wajib dipilih (organik, anorganik_daur_ulang, residu).',
            'berat_kg.required' => 'Berat setoran sampah (kg) wajib diisi.',
            'tanggal_setor.required' => 'Tanggal setoran wajib diisi (format: YYYY-MM-DD).',
        ];
    }
}
