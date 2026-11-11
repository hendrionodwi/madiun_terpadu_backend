<?php

namespace App\Http\Requests\Cms;

use App\Models\BroadcastAnnouncement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class SaveAnnouncementRequest extends FormRequest
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
        $isCreate = $this->isMethod('post');

        return [
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:200'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:5000'],
            'audience_role' => ['nullable', 'string', 'in:all,warga,petugas_lapangan,pengelola_tps3r,pemrakarsa'],
            'priority' => [$isCreate ? 'required' : 'sometimes', 'string', 'in:low,normal,high,urgent'],
            'status' => ['nullable', 'string', 'in:draft,scheduled,published,archived'],
            'kategori' => ['nullable', 'string', 'in:informasi,jadwal,himbauan,penting'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_portal' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Enforce consistent publication window constraints for scheduled and expiring announcements.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['published_at', 'expires_at'])) {
                    return;
                }

                $existing = $this->route('broadcastAnnouncement');
                $existingModel = $existing instanceof BroadcastAnnouncement ? $existing : null;

                $status = $this->filled('status')
                    ? (string) $this->input('status')
                    : ($existingModel?->status ?? 'published');

                $publishedAt = $this->exists('published_at')
                    ? ($this->filled('published_at') ? Carbon::parse((string) $this->input('published_at')) : null)
                    : $existingModel?->published_at;

                $expiresAt = $this->exists('expires_at')
                    ? ($this->filled('expires_at') ? Carbon::parse((string) $this->input('expires_at')) : null)
                    : $existingModel?->expires_at;

                if ($status === 'scheduled' && $publishedAt === null) {
                    $validator->errors()->add('published_at', 'Tanggal publikasi wajib diisi jika status pengumuman terjadwal.');
                }

                if ($publishedAt !== null && $expiresAt !== null && $expiresAt->lt($publishedAt)) {
                    $validator->errors()->add('expires_at', 'Tanggal kedaluwarsa harus sama dengan atau setelah tanggal publikasi.');
                }
            },
        ];
    }
}
