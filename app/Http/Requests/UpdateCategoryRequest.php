<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CategoryType;
use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return $category !== null && $this->user()?->can('update', $category) === true;
    }

    /**
     * Handle a failed authorization attempt.
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
        /** @var Category $category */
        $category = $this->route('category');
        $userId = (int) $this->user()?->id;
        $categoryTypeValue = $category->type->value;
        $type = $this->input('type', $categoryTypeValue);

        return [
            'name' => [
                'required',
                'string',
                'max:60',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('user_id', $userId)->where('type', $type))
                    ->ignore($category->id),
            ],
            'type' => [
                'required',
                Rule::enum(CategoryType::class),
                function (string $attribute, mixed $value, Closure $fail) use ($category, $categoryTypeValue): void {
                    if ($category->isTypeLocked() && $value !== $categoryTypeValue) {
                        $fail('The category type cannot be changed once it has transactions, budgets or recurring rules.');
                    }
                },
            ],
            'color' => [
                'required',
                'string',
                Rule::in(Category::PRESET_COLORS),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists for this type.',
            'color.in' => 'Please select one of the 8 preset colors.',
        ];
    }
}
