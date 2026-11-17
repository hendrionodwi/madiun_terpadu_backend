<?php

namespace App\Http\Requests\Cms;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
            'hero_mockup_image' => ['sometimes', 'nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'remove_hero_mockup_image' => ['sometimes', 'boolean'],
            'hero_background_image' => ['sometimes', 'nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'remove_hero_background_image' => ['sometimes', 'boolean'],
            'hero_cta_primary_text' => ['sometimes', 'string', 'max:60'],
            'hero_cta_primary_link' => $this->portalLinkRules(255),
            'hero_cta_secondary_text' => ['sometimes', 'string', 'max:60'],
            'hero_cta_secondary_link' => $this->portalLinkRules(255),
            'hero_app_badge_text' => ['sometimes', 'string', 'max:80'],
            'operational_status_label' => ['sometimes', 'string', 'max:120'],
            'contact_address' => ['sometimes', 'string', 'max:255'],
            'contact_email' => ['sometimes', 'email', 'max:150'],
            'contact_phone' => ['sometimes', 'string', 'max:50'],
            'transparency_note' => ['sometimes', 'string', 'max:600'],
            'announcement_banner_enabled' => ['sometimes', 'boolean'],
            'show_banner_slider' => ['sometimes', 'boolean'],
            'show_stats_section' => ['sometimes', 'boolean'],
            'show_education_section' => ['sometimes', 'boolean'],
            'show_marketplace_section' => ['sometimes', 'boolean'],
            'show_app_download_section' => ['sometimes', 'boolean'],
            'app_download_title' => ['sometimes', 'string', 'max:150'],
            'app_download_subtitle' => ['sometimes', 'string', 'max:600'],
            'app_download_button_text' => ['sometimes', 'string', 'max:60'],
            'app_download_url' => $this->portalLinkRules(500, nullable: true),
            'app_download_badge' => ['sometimes', 'string', 'max:100'],
            'app_qr_custom_image' => ['sometimes', 'nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
            'remove_app_qr_custom_image' => ['sometimes', 'boolean'],
            'service_steps' => ['sometimes', 'array', 'max:8'],
            'service_steps.*.step' => ['required_with:service_steps', 'integer', 'min:1'],
            'service_steps.*.title' => ['required_with:service_steps', 'string', 'max:100'],
            'service_steps.*.description' => ['required_with:service_steps', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function portalLinkRules(int $maxLength, bool $nullable = false): array
    {
        $rules = ['sometimes'];

        if ($nullable) {
            $rules[] = 'nullable';
        }

        $rules[] = 'string';
        $rules[] = 'max:'.$maxLength;
        $rules[] = function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $isFragment = Str::startsWith($value, '#');
            $isLocalPath = Str::startsWith($value, '/') && ! Str::startsWith($value, '//');
            $isExternalUrl = Str::startsWith($value, ['http://', 'https://'])
                && filter_var($value, FILTER_VALIDATE_URL) !== false;
            $hasUnsafeWhitespace = preg_match('/[\x00-\x20\x7F]/u', $value) === 1;

            if ((! $isFragment && ! $isLocalPath && ! $isExternalUrl) || $hasUnsafeWhitespace) {
                $fail('Tautan harus berupa URL HTTP/HTTPS, path lokal, atau anchor halaman.');
            }
        };

        return $rules;
    }
}
