<x-app-layout title="Add transaction">
    <div style="max-width: 480px; margin: 0 auto;">
        <div style="margin-bottom: var(--space-6);">
            <h1 class="page-title">Add transaction</h1>
            <p class="section-subtitle">Record your personal income or expense entry.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('transactions.store') }}" data-transaction-form>
                @csrf

                {{-- 1. Type toggle --}}
                <div style="margin-bottom: var(--space-4);">
                    <label class="field-label" id="label-type-toggle">Transaction type</label>
                    <div class="type-toggle-container" role="radiogroup" aria-labelledby="label-type-toggle">
                        <label class="type-toggle-label">
                            <input
                                type="radio"
                                name="type_toggle"
                                value="expense"
                                class="type-toggle-input"
                                {{ old('type_toggle', 'expense') === 'expense' ? 'checked' : '' }}
                            >
                            <span>Expense</span>
                        </label>
                        <label class="type-toggle-label">
                            <input
                                type="radio"
                                name="type_toggle"
                                value="income"
                                class="type-toggle-input"
                                {{ old('type_toggle') === 'income' ? 'checked' : '' }}
                            >
                            <span>Income</span>
                        </label>
                    </div>
                </div>

                {{-- 2. Category select --}}
                <x-field name="category_id" label="Category" required="true">
                    <select
                        name="category_id"
                        id="category_id"
                        class="field-input"
                        data-category-select
                        required
                    >
                        <option value="">Select a category</option>
                        <optgroup label="Expense Categories">
                            @foreach ($categories->where('type.value', 'expense') as $category)
                                <option
                                    value="{{ $category->id }}"
                                    data-type="expense"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Income Categories">
                            @foreach ($categories->where('type.value', 'income') as $category)
                                <option
                                    value="{{ $category->id }}"
                                    data-type="income"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </x-field>

                {{-- 3. Amount (visually largest field) --}}
                <x-field name="amount" label="Amount" required="true" suffix="{{ $userCurrency }}">
                    <input
                        type="text"
                        name="amount"
                        id="amount"
                        class="field-input transaction-amount-large"
                        inputmode="decimal"
                        placeholder="0.00"
                        value="{{ old('amount') }}"
                        required
                    >
                </x-field>

                {{-- 4. Date --}}
                <x-field name="transaction_date" label="Date" required="true">
                    <input
                        type="date"
                        name="transaction_date"
                        id="transaction_date"
                        class="field-input"
                        min="2000-01-01"
                        max="{{ $today }}"
                        value="{{ old('transaction_date', $today) }}"
                        required
                    >
                </x-field>

                {{-- 5. Description --}}
                <x-field name="description" label="Description" help="Optional note or payee (max 255 characters).">
                    <input
                        type="text"
                        name="description"
                        id="description"
                        class="field-input"
                        maxlength="255"
                        placeholder="e.g. Weekly grocery shopping"
                        value="{{ old('description') }}"
                    >
                </x-field>

                {{-- Actions --}}
                <div style="display: flex; flex-direction: column; gap: var(--space-3); margin-top: var(--space-6);">
                    <x-button variant="primary" type="submit" style="width: 100%;">
                        Save transaction
                    </x-button>
                    <div style="display: flex; gap: var(--space-3); justify-content: space-between;">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="action" value="add_another" class="btn btn-secondary">
                            Save and add another
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
