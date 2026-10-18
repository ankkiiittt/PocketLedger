<x-app-layout title="Edit category">
    <div style="max-width: 480px; margin: 0 auto;">
        <div style="margin-bottom: var(--space-6);">
            <h1 class="page-title">Edit category</h1>
            <p class="section-subtitle">Update details for '{{ $category->name }}'.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf
                @method('PUT')

                <x-field name="name" label="Category name" required="true" help="Between 1 and 60 characters. Unique per type.">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="field-input"
                        maxlength="60"
                        value="{{ old('name', $category->name) }}"
                        required
                        autofocus
                    >
                </x-field>

                @if ($isTypeLocked)
                    <input type="hidden" name="type" value="{{ $category->type->value }}">
                    <x-field name="type" label="Type" help="Type cannot be changed once the category has transactions, budgets or recurring rules.">
                        <input
                            type="text"
                            class="field-input"
                            value="{{ ucfirst($category->type->value) }}"
                            disabled
                            aria-readonly="true"
                        >
                    </x-field>
                @else
                    <x-field name="type" label="Type" required="true">
                        <select name="type" id="type" class="field-input" required>
                            <option value="expense" {{ old('type', $category->type->value) === 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="income" {{ old('type', $category->type->value) === 'income' ? 'selected' : '' }}>Income</option>
                        </select>
                    </x-field>
                @endif

                <x-field name="color" label="Category colour" required="true" help="Select one of the 8 ledger colours.">
                    <div class="color-swatches" role="radiogroup" aria-label="Category colour choices">
                        @foreach ($presetColors as $colorHex)
                            <label class="color-swatch-label">
                                <input
                                    type="radio"
                                    name="color"
                                    value="{{ $colorHex }}"
                                    class="color-swatch-input"
                                    {{ old('color', $category->color) === $colorHex ? 'checked' : '' }}
                                    required
                                >
                                <span class="color-swatch-circle" style="background-color: {{ $colorHex }};" title="{{ $colorHex }}"></span>
                            </label>
                        @endforeach
                    </div>
                </x-field>

                <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6); justify-content: flex-end;">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
                    <x-button variant="primary" type="submit">Save changes</x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
