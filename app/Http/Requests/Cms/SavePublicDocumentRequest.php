<?php

namespace App\Http\Requests\Cms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SavePublicDocumentRequest extends FormRequest
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
            'kategori' => [$isCreate ? 'required' : 'sometimes', 'string', 'in:panduan,regulasi,peta,laporan,sop'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file_dokumen' => [$isCreate ? 'required' : 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'tanggal_rilis' => ['nullable', 'date'],
            'hak_akses_label' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
        ];
    }
}
