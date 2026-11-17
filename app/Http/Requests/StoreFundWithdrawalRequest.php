<?php

namespace App\Http\Requests;

use App\Actions\CreateMarketplacePointSettlement;
use App\Models\FundWithdrawalRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFundWithdrawalRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->routeIs('dashboard.tps3r.withdrawals.store')) {
            $this->merge(['source' => 'marketplace_points']);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', FundWithdrawalRequest::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedSources = $this->routeIs('dashboard.tps3r.withdrawals.store')
            ? ['marketplace_points']
            : ['points'];

        return [
            'source' => ['required', Rule::in($allowedSources)],
            'points_used' => [
                'nullable',
                'integer',
                'min:'.config('finance.points.minimum_redemption_points'),
                Rule::requiredIf(fn (): bool => $this->input('source') === 'points'),
            ],
            'amount' => [
                'nullable',
                'integer',
                'min:'.CreateMarketplacePointSettlement::MINIMUM_SETTLEMENT_AMOUNT,
                Rule::requiredIf(fn (): bool => $this->input('source') === 'marketplace_points'),
            ],
            'destination_type' => [
                'required',
                Rule::in($this->input('source') === 'marketplace_points'
                    ? ['e_wallet', 'bank_transfer']
                    : ['cash', 'e_wallet', 'bank_transfer']),
            ],
            'destination_provider' => ['nullable', 'string', 'max:50', 'required_if:destination_type,e_wallet,bank_transfer'],
            'destination_account' => ['nullable', 'string', 'max:100', 'required_if:destination_type,e_wallet,bank_transfer'],
            'destination_account_name' => ['nullable', 'string', 'max:100', 'required_if:destination_type,e_wallet,bank_transfer'],
            'cash_pickup_location' => ['nullable', 'string', 'max:255'],
            'requester_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal settlement wajib diisi.',
            'amount.integer' => 'Nominal settlement harus berupa angka Rupiah bulat.',
            'amount.min' => 'Nominal settlement minimal Rp10.000.',
            'destination_provider.required_if' => 'Penyedia e-wallet atau nama bank wajib dipilih.',
            'destination_account.required_if' => 'Nomor akun tujuan wajib diisi.',
            'destination_account_name.required_if' => 'Nama pemilik akun tujuan wajib diisi.',
        ];
    }
}
