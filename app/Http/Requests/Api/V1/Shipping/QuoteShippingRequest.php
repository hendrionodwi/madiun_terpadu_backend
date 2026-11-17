<?php

namespace App\Http\Requests\Api\V1\Shipping;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QuoteShippingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isWarga() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'origin_id' => ['required', 'integer', 'min:1'],
            'destination_id' => ['required', 'integer', 'min:1'],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:30000'],
            'couriers' => ['sometimes', 'string', 'max:100', 'regex:/^[a-z0-9_:-]+$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'origin_id.required' => 'Lokasi asal pengiriman wajib dipilih.',
            'destination_id.required' => 'Lokasi tujuan pengiriman wajib dipilih.',
            'weight_grams.required' => 'Berat kiriman wajib diisi.',
            'weight_grams.max' => 'Berat kiriman maksimal 30 kg per transaksi.',
            'couriers.regex' => 'Format daftar kurir tidak valid.',
        ];
    }
}
