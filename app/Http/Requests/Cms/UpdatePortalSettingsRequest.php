<?php

namespace App\Http\Requests\Cms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePortalSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdminDlh();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hero_kicker' => ['sometimes', 'string', 'max:120'],
            'hero_title' => ['sometimes', 'string', 'max:200'],
            'hero_subtitle' => ['sometimes', 'string', 'max:600'],
            'hero_badge_text' => ['sometimes', 'string', 'max:200'],
            'operational_status_label' => ['sometimes', 'string', 'max:120'],
            'contact_address' => ['sometimes', 'string', 'max:255'],
            'contact_email' => ['sometimes', 'email', 'max:150'],
            'contact_phone' => ['sometimes', 'string', 'max:50'],
            'transparency_note' => ['sometimes', 'string', 'max:600'],
            'announcement_banner_enabled' => ['sometimes', 'boolean'],
            'show_stats_section' => ['sometimes', 'boolean'],
            'show_education_section' => ['sometimes', 'boolean'],
            'show_marketplace_section' => ['sometimes', 'boolean'],
            'service_steps' => ['sometimes', 'array', 'max:8'],
            'service_steps.*.step' => ['required_with:service_steps', 'integer', 'min:1'],
            'service_steps.*.title' => ['required_with:service_steps', 'string', 'max:100'],
            'service_steps.*.description' => ['required_with:service_steps', 'string', 'max:300'],
        ];
    }
}
