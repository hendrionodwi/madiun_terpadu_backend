<?php

namespace App\Http\Requests\Api\V1\Shipping;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchDestinationRequest extends FormRequest
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
            'search' => ['required', 'string', 'min:3', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'offset' => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'search.required' => 'Kata pencarian tujuan wajib diisi.',
            'search.min' => 'Kata pencarian tujuan minimal 3 karakter.',
            'limit.max' => 'Maksimal 20 tujuan dapat dimuat dalam satu permintaan.',
        ];
    }
}
