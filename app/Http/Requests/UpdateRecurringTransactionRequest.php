<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Frequency;
use App\Models\RecurringTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurringTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var RecurringTransaction|null $recurringTransaction */
        $recurringTransaction = $this->route('recurringTransaction');

        return $recurringTransaction !== null && $this->user()?->can('update', $recurringTransaction) === true;
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
        /** @var RecurringTransaction $recurringTransaction */
        $recurringTransaction = $this->route('recurringTransaction');
        $userId = (int) $this->user()?->id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($userId, $recurringTransaction) {
                    $query->where('user_id', $userId)
                        ->where(function ($q) use ($recurringTransaction) {
                            $q->where('is_archived', false)
                                ->orWhere('id', $recurringTransaction->category_id);
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
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'frequency' => [
                'required',
                Rule::enum(Frequency::class),
            ],
            'start_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'end_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Custom error messages.
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
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ];
    }
}
