<?php

namespace App\Http\Requests;

use App\Enums\FundWithdrawalStatus;
use App\Models\FundWithdrawalRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFundWithdrawalReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('review', $this->route('fundWithdrawalRequest')) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $withdrawal = $this->route('fundWithdrawalRequest');
        $requiresAllocation = $withdrawal instanceof FundWithdrawalRequest
            && in_array($withdrawal->source, ['points', 'marketplace_points'], true);

        return [
            'status' => ['required', Rule::enum(FundWithdrawalStatus::class)->only([
                FundWithdrawalStatus::Approved,
                FundWithdrawalStatus::Rejected,
            ])],
            'reward_fund_allocation_id' => [
                'nullable',
                'integer',
                'exists:reward_fund_allocations,id',
                Rule::requiredIf(fn (): bool => $this->input('status') === FundWithdrawalStatus::Approved->value
                    && $requiresAllocation),
            ],
            'admin_note' => ['nullable', 'string', 'max:500', 'required_if:status,rejected'],
        ];
    }
}
