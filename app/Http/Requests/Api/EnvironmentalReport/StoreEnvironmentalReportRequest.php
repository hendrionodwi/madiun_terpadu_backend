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
        $isUpdate = $this->isMethod('PATCH') || $this->isMethod('PUT');
        $isDraft = $this->boolean('is_draft') || $this->input('status') === 'draft';
        $requiredRule = $isUpdate ? 'sometimes' : 'required';

        return [
            'judul_laporan' => array_values(array_filter([$requiredRule, $isUpdate ? 'required' : null, 'string', 'max:255'])),
            'periode_semester' => array_values(array_filter([$requiredRule, $isUpdate ? 'required' : null, 'string', 'in:Semester 1 (Jan - Jun),Semester 2 (Jul - Des)'])),
            'tahun' => array_values(array_filter([$requiredRule, $isUpdate ? 'required' : null, 'integer', 'min:2020', 'max:2035'])),
            'jenis_dokumen' => array_values(array_filter([$requiredRule, $isUpdate ? 'required' : null, 'string', 'in:SPPL,UKL-UPL,AMDAL'])),
            'timbulan_sampah_kg' => ['nullable', 'numeric', 'min:0'],
            'metode_pengelolaan' => ['nullable', 'string', 'max:2000'],
            'monitoring_data' => ['nullable', 'array'],
            'is_draft' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:draft,menunggu_verifikasi'],
            'file_laporan' => [$isUpdate || $isDraft ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
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
