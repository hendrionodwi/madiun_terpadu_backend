<?php

namespace App\Http\Requests\Web\Admin;

use App\Models\MasterData;
use App\Services\RajaOngkirService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShippingCourierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdminDlh() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hasDatabaseSettings = MasterData::query()
            ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
            ->exists();
        $blockedFallbackCodes = $hasDatabaseSettings
            ? []
            : array_values(array_unique([
                ...array_keys(RajaOngkirService::courierCatalog()),
                ...RajaOngkirService::fallbackCourierCodes(),
            ]));

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::notIn($blockedFallbackCodes),
                Rule::unique('master_data', 'code')
                    ->where(fn ($query) => $query->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => mb_strtolower(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
            'description' => trim((string) $this->input('description')),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode API kurir wajib diisi.',
            'code.regex' => 'Kode API hanya boleh berisi huruf kecil, angka, garis bawah, atau tanda hubung.',
            'code.not_in' => 'Kode kurir tersebut sudah tersedia.',
            'code.unique' => 'Kode kurir tersebut sudah tersedia.',
            'name.required' => 'Nama kurir wajib diisi.',
            'description.max' => 'Deskripsi kurir maksimal 500 karakter.',
        ];
    }
}
