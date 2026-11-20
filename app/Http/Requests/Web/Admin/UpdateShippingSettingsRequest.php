<?php

namespace App\Http\Requests\Web\Admin;

use App\Models\MasterData;
use App\Services\RajaOngkirService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShippingSettingsRequest extends FormRequest
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
        $databaseCourierCodes = MasterData::query()
            ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn (mixed $code): string => (string) $code)
            ->all();
        $availableCourierCodes = $databaseCourierCodes !== []
            ? $databaseCourierCodes
            : array_values(array_unique([
                ...array_keys(RajaOngkirService::courierCatalog()),
                ...RajaOngkirService::fallbackCourierCodes(),
            ]));

        return [
            'couriers' => ['required', 'array', 'min:1'],
            'couriers.*' => [
                'required',
                'string',
                'distinct',
                Rule::in($availableCourierCodes),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'couriers.required' => 'Pilih minimal satu kurir pengiriman.',
            'couriers.array' => 'Daftar kurir pengiriman tidak valid.',
            'couriers.min' => 'Pilih minimal satu kurir pengiriman.',
            'couriers.*.in' => 'Terdapat kurir yang tidak didukung sistem.',
            'couriers.*.distinct' => 'Kurir pengiriman tidak boleh dipilih lebih dari sekali.',
        ];
    }
}
