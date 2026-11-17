<?php

namespace App\Http\Requests\Api\V1\DeliveryZone;

use App\Models\Tps3rDeliveryZone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewDeliveryZoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdminDlh() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                Tps3rDeliveryZone::STATUS_APPROVED,
                Tps3rDeliveryZone::STATUS_REJECTED,
            ])],
            'rejection_reason' => [
                Rule::requiredIf($this->input('status') === Tps3rDeliveryZone::STATUS_REJECTED),
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.in' => 'Keputusan hanya dapat berupa disetujui atau ditolak.',
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ];
    }
}
