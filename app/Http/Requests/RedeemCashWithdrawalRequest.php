<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RedeemCashWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdminDlh() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'claim_code' => Str::upper(trim((string) $this->input('claim_code'))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'claim_code' => ['required', 'string', 'regex:/^TUN-[A-Z0-9]{5}-[A-Z0-9]{5}$/'],
            'identity_verified' => ['accepted'],
            'payout_reference' => ['required', 'string', 'max:100', Rule::unique('fund_withdrawal_requests', 'payout_reference')],
            'payout_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'payout_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'claim_code.required' => 'Kode transaksi wajib diisi.',
            'claim_code.regex' => 'Format kode transaksi tidak valid.',
            'identity_verified.accepted' => 'Identitas warga wajib diperiksa sebelum uang diserahkan.',
            'payout_reference.required' => 'Nomor kwitansi atau referensi kas wajib dicatat.',
            'payout_reference.unique' => 'Nomor kwitansi atau referensi kas sudah pernah digunakan.',
            'payout_proof.required' => 'Bukti serah-terima tunai wajib diunggah.',
        ];
    }
}
