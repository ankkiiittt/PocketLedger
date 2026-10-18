<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Frequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $userId = (int) $this->user()?->id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('is_archived', false);
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
                'after_or_equal:today',
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
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ];
    }
}
