<?php

namespace App\Http\Requests\Api\V1\DeliveryZone;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreDeliveryZoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && $user->isPengelolaTps3r()
            && $user->pengelolaTps3rProfile !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kelurahan' => ['required', 'string', 'max:100', $this->uniqueZoneRule()],
            'kecamatan' => ['required', 'string', Rule::in(['Kartoharjo', 'Manguharjo', 'Taman'])],
            'delivery_fee' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kelurahan' => Str::title(trim((string) $this->input('kelurahan'))),
            'kecamatan' => Str::title(trim((string) $this->input('kecamatan'))),
        ]);
    }

    protected function uniqueZoneRule(): Unique
    {
        $profileId = $this->user()?->pengelolaTps3rProfile?->id;

        return Rule::unique('tps3r_delivery_zones', 'kelurahan')
            ->where(fn ($query) => $query
                ->where('pengelola_tps3r_profile_id', $profileId)
                ->where('kecamatan', $this->input('kecamatan')));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'kelurahan.unique' => 'Kelurahan tersebut sudah terdaftar sebagai wilayah pengiriman TPS3R Anda.',
            'kecamatan.in' => 'Kecamatan harus berada di wilayah Kota Madiun.',
            'delivery_fee.max' => 'Biaya pengiriman maksimal Rp1.000.000.',
        ];
    }
}
