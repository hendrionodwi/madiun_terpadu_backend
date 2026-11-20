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
            'origin_id' => ['nullable', 'required_without:seller_id', 'integer', 'min:1'],
            'seller_id' => ['nullable', 'required_without:origin_id', 'integer', 'exists:users,id'],
            'destination_id' => ['required', 'integer', 'min:1'],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:30000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'origin_id.required' => 'Lokasi asal pengiriman wajib dipilih.',
            'seller_id.required_without' => 'TPS3R asal pengiriman wajib dipilih.',
            'seller_id.exists' => 'TPS3R asal pengiriman tidak ditemukan.',
            'destination_id.required' => 'Lokasi tujuan pengiriman wajib dipilih.',
            'weight_grams.required' => 'Berat kiriman wajib diisi.',
            'weight_grams.max' => 'Berat kiriman maksimal 30 kg per transaksi.',
        ];
    }
}
