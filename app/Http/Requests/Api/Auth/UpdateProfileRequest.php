<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status_tugas' => ['nullable', 'string', 'in:standby,bertugas,istirahat,off'],
            'jenis_kendaraan' => ['nullable', 'string', 'max:100'],
            'plat_nomor' => ['nullable', 'string', 'max:30'],
            'nama_tps3r' => ['nullable', 'string', 'max:255'],
            'nama_instansi' => ['nullable', 'string', 'max:255'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
        ];
    }
}
