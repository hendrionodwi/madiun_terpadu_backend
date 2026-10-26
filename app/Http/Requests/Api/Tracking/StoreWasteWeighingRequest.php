<?php

namespace App\Http\Requests\Api\Tracking;

use App\Models\MasterData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWasteWeighingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $defaultCategories = ['organik', 'anorganik_plastik', 'anorganik_kertas', 'anorganik_logam', 'residu'];
        $masterCategories = MasterData::query()
            ->where('group', 'waste_category')
            ->where('is_active', true)
            ->get(['code', 'name']);

        $allowedCategories = array_values(array_unique(array_filter(array_merge(
            $defaultCategories,
            $masterCategories->pluck('code')->all(),
            $masterCategories->pluck('name')->all(),
        ))));

        return [
            'warga_id' => ['required', 'integer', 'exists:users,id'],
            'pickup_schedule_id' => ['nullable', 'integer', 'exists:pickup_schedules,id'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.jenis_sampah' => ['required_with:items', 'string', Rule::in($allowedCategories)],
            'items.*.berat_kg' => ['required_with:items', 'numeric', 'min:0.1', 'max:9999.99'],
            'items.*.poin_per_kg' => ['nullable', 'integer', 'min:1'],
            'items.*.catatan' => ['nullable', 'string', 'max:500'],
            'items.*.foto_bukti' => ['nullable'],
            'jenis_sampah' => ['required_without:items', 'nullable', 'string', Rule::in($allowedCategories)],
            'berat_kg' => ['required_without:items', 'nullable', 'numeric', 'min:0.1', 'max:9999.99'],
            'poin_per_kg' => ['nullable', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'foto_bukti' => ['nullable'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'warga_id.required' => 'ID Warga wajib dipilih.',
            'warga_id.exists' => 'Data warga tidak ditemukan.',
            'jenis_sampah.required_without' => 'Jenis sampah wajib dipilih jika tidak mengirim daftar item.',
            'jenis_sampah.in' => 'Jenis sampah tidak valid atau belum terdaftar di Data Master.',
            'berat_kg.required_without' => 'Berat hasil timbangan (kg) wajib diisi jika tidak mengirim daftar item.',
            'berat_kg.min' => 'Berat minimal timbangan adalah 0.1 kg.',
            'items.*.jenis_sampah.required_with' => 'Jenis sampah pada setiap item wajib dipilih.',
            'items.*.jenis_sampah.in' => 'Jenis sampah item tidak valid atau belum terdaftar di Data Master.',
            'items.*.berat_kg.required_with' => 'Berat pada setiap item wajib diisi.',
            'items.*.berat_kg.min' => 'Berat minimal item timbangan adalah 0.1 kg.',
        ];
    }
}
