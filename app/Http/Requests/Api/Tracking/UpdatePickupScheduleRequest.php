<?php

namespace App\Http\Requests\Api\Tracking;

use App\Models\PickupSchedule;
use App\Models\RouteAssignment;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdatePickupScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('kategori_sampah') && is_array($this->input('kategori_sampah')) && ! $this->filled('jenis_sampah')) {
            $this->merge([
                'jenis_sampah' => implode(', ', $this->input('kategori_sampah')),
            ]);
        } elseif (is_array($this->input('jenis_sampah'))) {
            $this->merge([
                'jenis_sampah' => implode(', ', $this->input('jenis_sampah')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_wilayah_rute' => ['nullable', 'string', 'max:50'],
            'nama_wilayah_rute' => ['nullable', 'string', 'max:255'],
            'jenis_sampah' => ['nullable', 'string', 'max:255'],
            'kategori_sampah' => ['nullable', 'array'],
            'kategori_sampah.*' => ['string', 'max:100'],
            'tanggal_penjemputan' => ['nullable', 'date_format:Y-m-d'],
            'waktu_mulai' => ['nullable', 'string', 'max:10'],
            'waktu_selesai' => ['nullable', 'string', 'max:10'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'alamat_penjemputan' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var User|null $user */
            $user = $this->user();
            if (! $user || ! $user->isWarga()) {
                return;
            }

            /** @var PickupSchedule|null $schedule */
            $schedule = $this->route('pickupSchedule');
            $tanggal = $this->input('tanggal_penjemputan', $schedule?->tanggal_penjemputan?->toDateString() ?? $schedule?->tanggal_penjemputan);

            if (! $tanggal) {
                return;
            }

            $today = now()->toDateString();
            if ($tanggal < $today) {
                $validator->errors()->add('tanggal_penjemputan', 'Tanggal penjemputan tidak boleh tanggal yang sudah terlewat.');

                return;
            }

            if ($tanggal === $today) {
                $routeCode = $this->input('id_wilayah_rute', $schedule?->id_wilayah_rute);
                $route = RouteAssignment::where('route_code', $routeCode)->first();
                $startTimeStr = $route?->start_time ?? $this->input('waktu_mulai', $schedule?->waktu_mulai);

                if ($startTimeStr) {
                    try {
                        $startTime = Carbon::parse($today.' '.$startTimeStr);
                        $cutOffTime = $startTime->copy()->subHour();

                        if (now()->greaterThanOrEqualTo($cutOffTime)) {
                            $validator->errors()->add(
                                'tanggal_penjemputan',
                                "Pemesanan penjemputan untuk hari ini telah ditutup karena melewati batas cut-off operasional armada (maksimal 1 jam sebelum jam operasional {$startTimeStr}). Silakan pilih tanggal besok atau hari berikutnya."
                            );
                        }
                    } catch (\Throwable $e) {
                        // ignore parse exception
                    }
                }
            }
        });
    }
}
