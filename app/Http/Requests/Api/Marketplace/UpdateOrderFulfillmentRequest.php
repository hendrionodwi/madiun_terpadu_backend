<?php

namespace App\Http\Requests\Api\Marketplace;

use App\Enums\OrderFulfillmentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderFulfillmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPengelolaTps3r() ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(OrderFulfillmentStatus::class)],
            'catatan_status' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.required' => 'Status pemrosesan wajib dipilih.',
            'status.enum' => 'Status pemrosesan yang dipilih tidak valid.',
            'catatan_status.max' => 'Catatan status maksimal 500 karakter.',
        ];
    }
}
