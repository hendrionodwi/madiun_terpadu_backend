<?php

namespace App\Http\Requests\Api\V1\DeliveryZone;

use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateDeliveryZoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $deliveryZone = $this->route('tps3rDeliveryZone');

        return $user instanceof User
            && $user->isPengelolaTps3r()
            && $deliveryZone instanceof Tps3rDeliveryZone
            && $deliveryZone->pengelola_tps3r_profile_id === $user->pengelolaTps3rProfile?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Tps3rDeliveryZone $deliveryZone */
        $deliveryZone = $this->route('tps3rDeliveryZone');
        $profileId = $this->user()?->pengelolaTps3rProfile?->id;

        return [
            'kelurahan' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('tps3r_delivery_zones', 'kelurahan')
                    ->ignore($deliveryZone)
                    ->where(fn ($query) => $query
                        ->where('pengelola_tps3r_profile_id', $profileId)
                        ->where('kecamatan', $this->input('kecamatan', $deliveryZone->kecamatan))),
            ],
            'kecamatan' => ['sometimes', 'required', 'string', Rule::in(['Kartoharjo', 'Manguharjo', 'Taman'])],
            'delivery_fee' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('kelurahan')) {
            $normalized['kelurahan'] = Str::title(trim((string) $this->input('kelurahan')));
        }

        if ($this->has('kecamatan')) {
            $normalized['kecamatan'] = Str::title(trim((string) $this->input('kecamatan')));
        }

        $this->merge($normalized);
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
