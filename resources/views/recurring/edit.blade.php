<x-app-layout title="Edit recurring transaction">
    <div style="max-width: 480px; margin: 0 auto;">
        <div style="margin-bottom: var(--space-6);">
            <h1 class="page-title">Edit recurring transaction</h1>
            <p class="section-subtitle">Changes will apply to future scheduled occurrences only.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('recurring.update', $rule) }}">
                @csrf
                @method('PUT')

                {{-- Category select --}}
                <x-field name="category_id" label="Category" required="true">
                    <select
                        name="category_id"
                        id="category_id"
                        class="field-input"
                        required
                    >
                        <option value="">Select a category</option>
                        <optgroup label="Expense Categories">
                            @foreach ($categories->where('type.value', 'expense') as $category)
                                <option
                                    value="{{ $category->id }}"
                                    {{ old('category_id', $rule->category_id) == $category->id ? 'selected' : '' }}
                                >
                                    {{ $category->name }} {{ $category->is_archived ? '(Archived)' : '' }}
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Income Categories">
                            @foreach ($categories->where('type.value', 'income') as $category)
                                <option
                                    value="{{ $category->id }}"
                                    {{ old('category_id', $rule->category_id) == $category->id ? 'selected' : '' }}
                                >
                                    {{ $category->name }} {{ $category->is_archived ? '(Archived)' : '' }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </x-field>

                {{-- Amount --}}
                <x-field name="amount" label="Amount" required="true" suffix="{{ $userCurrency }}">
                    <input
                        type="text"
                        name="amount"
                        id="amount"
                        class="field-input transaction-amount-large"
                        inputmode="decimal"
                        placeholder="0.00"
                        value="{{ old('amount', $rule->amount) }}"
                        required
                    >
                </x-field>

                {{-- Frequency --}}
                <x-field name="frequency" label="Frequency" required="true">
                    <select
                        name="frequency"
                        id="frequency"
                        class="field-input"
                        required
                    >
                        @foreach ($frequencies as $freq)
                            <option
                                value="{{ $freq->value }}"
                                {{ old('frequency', $rule->frequency->value) === $freq->value ? 'selected' : '' }}
                            >
                                {{ $freq->label() }}
                            </option>
                        @endforeach
                    </select>
                </x-field>

                {{-- Start date --}}
                <x-field name="start_date" label="Start date" required="true">
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        class="field-input"
                        value="{{ old('start_date', $rule->start_date->format('Y-m-d')) }}"
                        required
                    >
                </x-field>

                {{-- End date --}}
                <x-field name="end_date" label="End date (optional)">
                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        class="field-input"
                        value="{{ old('end_date', $rule->end_date?->format('Y-m-d')) }}"
                    >
                </x-field>

                {{-- Description --}}
                <x-field name="description" label="Description (optional)">
                    <input
                        type="text"
                        name="description"
                        id="description"
                        class="field-input"
                        placeholder="e.g. Apartment rent, Netflix subscription"
                        value="{{ old('description', $rule->description) }}"
                        maxlength="255"
                    >
                </x-field>

                {{-- Active status --}}
                <div style="margin-bottom: var(--space-5);">
                    <label style="display: inline-flex; align-items: center; gap: var(--space-2); cursor: pointer;">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $rule->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                            style="width: 18px; height: 18px; accent-color: var(--color-primary);"
                        >
                        <span style="font-size: 0.9375rem; font-weight: 500;">Active</span>
                    </label>
                </div>

                {{-- Form Actions --}}
                <div class="form-actions" style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
                    <x-button variant="primary" type="submit" style="flex: 1;">Save changes</x-button>
                    <a href="{{ route('recurring.index') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
