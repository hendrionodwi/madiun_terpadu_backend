<?php

namespace App\Http\Requests\Api\EnvironmentalReport;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnvironmentalReportRequest extends FormRequest
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
            'judul_laporan' => ['required', 'string', 'max:255'],
            'periode_semester' => ['required', 'string', 'in:Semester 1 (Jan - Jun),Semester 2 (Jul - Des)'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2035'],
            'jenis_dokumen' => ['required', 'string', 'in:SPPL,UKL-UPL,AMDAL'],
            'timbulan_sampah_kg' => ['nullable', 'numeric', 'min:0'],
            'metode_pengelolaan' => ['nullable', 'string', 'max:2000'],
            'file_laporan' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'judul_laporan.required' => 'Judul laporan wajib diisi.',
            'periode_semester.in' => 'Periode semester tidak valid.',
            'jenis_dokumen.in' => 'Jenis dokumen harus SPPL, UKL-UPL, atau AMDAL.',
            'file_laporan.required' => 'File laporan wajib diunggah.',
            'file_laporan.mimes' => 'File laporan harus berformat PDF, DOC, atau DOCX.',
        ];
    }
}
