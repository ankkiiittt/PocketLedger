<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
        $type = $this->input('type');

        return [
            'name' => [
                'required',
                'string',
                'max:60',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('user_id', $userId)->where('type', $type)),
            ],
            'type' => [
                'required',
                Rule::enum(CategoryType::class),
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
