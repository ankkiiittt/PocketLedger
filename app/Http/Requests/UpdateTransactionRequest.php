<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Transaction|null $transaction */
        $transaction = $this->route('transaction');

        return $transaction !== null && $this->user()?->can('update', $transaction) === true;
    }

    /**
     * Handle failed authorization attempt with 404.
     */
    protected function failedAuthorization(): void
    {
        abort(404);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        /** @var Transaction $transaction */
        $transaction = $this->route('transaction');
        $userId = (int) $this->user()?->id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($userId, $transaction) {
                    $query->where('user_id', $userId)
                        ->where(function ($q) use ($transaction) {
                            $q->where('is_archived', false)
                                ->orWhere('id', $transaction->category_id);
                        });
                }),
            ],
            'amount' => [
                'required',
                'string',
                'regex:/^\d{1,9}(\.\d{1,2})?$/',
                'numeric',
                'min:0.01',
                'max:999999999.99',
            ],
            'transaction_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:2000-01-01',
                'before_or_equal:today',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'return_to' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category is invalid or inactive.',
            'amount.regex' => 'Amount must be a valid number with at most 2 decimal places.',
            'amount.min' => 'Amount must be at least 0.01.',
            'amount.max' => 'Amount must not exceed 999,999,999.99.',
            'transaction_date.before_or_equal' => 'Transaction date cannot be in the future.',
            'transaction_date.after_or_equal' => 'Transaction date must be on or after 2000-01-01.',
        ];
    }
}
