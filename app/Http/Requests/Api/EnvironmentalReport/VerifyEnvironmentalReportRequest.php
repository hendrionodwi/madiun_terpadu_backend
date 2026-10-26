<?php

namespace App\Http\Requests\Api\EnvironmentalReport;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyEnvironmentalReportRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:disetujui,perlu_revisi,perlu_cek_lapangan'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
            'assigned_petugas_id' => [
                'nullable',
                'required_if:status,perlu_cek_lapangan',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::PetugasLapangan->value)
                    ->where('is_active', true),
            ],
            'instruksi_lapangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
