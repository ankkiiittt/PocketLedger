<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetsRequest extends FormRequest
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
        /** @var User $user */
        $user = $this->user();
        $maxMonth = Carbon::now()->addMonths(12)->format('Y-m');

        return [
            'month' => [
                'required',
                'date_format:Y-m',
                'after_or_equal:2020-01',
                "before_or_equal:{$maxMonth}",
            ],
            'limits' => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, Closure $fail) use ($user): void {
                    if (! is_array($value)) {
                        return;
                    }

                    $categoryIds = array_keys($value);
                    $validIds = $user->categories()
                        ->expense()
                        ->whereIn('id', $categoryIds)
                        ->pluck('id')
                        ->all();

                    foreach ($categoryIds as $catId) {
                        if (! in_array((int) $catId, $validIds, true)) {
                            $fail("The category {$catId} is invalid or not an expense category for your account.");
                        }
                    }
                },
            ],
            'limits.*' => [
                'nullable',
                'string',
                'regex:/^\d{1,9}(\.\d{1,2})?$/',
                'numeric',
                'min:0.01',
                'max:999999999.99',
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
            'month.after_or_equal' => 'Budget month cannot be earlier than January 2020.',
            'month.before_or_equal' => 'Budget month cannot be more than 12 months in the future.',
            'limits.*.regex' => 'Each limit must be a valid amount with at most 2 decimal places.',
            'limits.*.min' => 'Each limit must be at least 0.01.',
            'limits.*.max' => 'Each limit must not exceed 999,999,999.99.',
        ];
    }
}
