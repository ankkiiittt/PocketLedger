<x-app-layout title="Add category">
    <div style="max-width: 480px; margin: 0 auto;">
        <div style="margin-bottom: var(--space-6);">
            <h1 class="page-title">Add category</h1>
            <p class="section-subtitle">Create a new income or expense category.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf

                <x-field name="name" label="Category name" required="true" help="Between 1 and 60 characters. Unique per type.">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="field-input"
                        maxlength="60"
                        value="{{ old('name') }}"
                        required
                        autofocus
                    >
                </x-field>

                <x-field name="type" label="Type" required="true">
                    <select name="type" id="type" class="field-input" required>
                        <option value="expense" {{ old('type', 'expense') === 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Income</option>
                    </select>
                </x-field>

                <x-field name="color" label="Category colour" required="true" help="Select one of the 8 ledger colours.">
                    <div class="color-swatches" role="radiogroup" aria-label="Category colour choices">
                        @foreach ($presetColors as $colorHex)
                            <label class="color-swatch-label">
                                <input
                                    type="radio"
                                    name="color"
                                    value="{{ $colorHex }}"
                                    class="color-swatch-input"
                                    {{ old('color', $presetColors[0]) === $colorHex ? 'checked' : '' }}
                                    required
                                >
                                <span class="color-swatch-circle" style="background-color: {{ $colorHex }};" title="{{ $colorHex }}"></span>
                            </label>
                        @endforeach
                    </div>
                </x-field>

                <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6); justify-content: flex-end;">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
                    <x-button variant="primary" type="submit">Create category</x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
