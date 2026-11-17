<?php

namespace App\Http\Requests;

use App\Enums\FundWithdrawalStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordFundWithdrawalPayoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('executePayout', $this->route('fundWithdrawalRequest')) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(FundWithdrawalStatus::class)->only([
                FundWithdrawalStatus::Processing,
                FundWithdrawalStatus::Paid,
                FundWithdrawalStatus::Failed,
            ])],
            'payout_reference' => ['nullable', 'string', 'max:100', 'required_if:status,paid'],
            'payout_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120', 'required_if:status,paid'],
            'payout_note' => ['nullable', 'string', 'max:500'],
            'failure_note' => ['nullable', 'string', 'max:500', 'required_if:status,failed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'payout_reference.required_if' => 'Nomor referensi transaksi wajib dicatat saat pembayaran berhasil.',
            'payout_proof.required_if' => 'Bukti pembayaran wajib diunggah saat pembayaran berhasil.',
            'failure_note.required_if' => 'Alasan kegagalan pembayaran wajib dicatat.',
        ];
    }
}
