<x-app-layout title="Budgets">
    <div class="budgets-header">
        <div>
            <h1 class="page-title">Monthly budgets</h1>
            <p class="section-subtitle">Plan and monitor your spending limits for each expense category.</p>
        </div>

        <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap;">
            <x-month-switcher :month-range="$monthRange" />

            <form method="POST" action="{{ route('budgets.copy') }}" style="display: inline;">
                @csrf
                <input type="hidden" name="month" value="{{ $monthRange->currentMonth }}">
                <x-button variant="secondary" type="submit">
                    <x-icon name="copy" size="sm" />
                    <span>Copy from previous month</span>
                </x-button>
            </form>
        </div>
    </div>

    {{-- Totals Strip --}}
    <div class="totals-strip">
        <div class="totals-item">
            <span class="totals-label">Total Budgeted</span>
            <span class="totals-value">
                <span data-budget-live-total>{{ \App\Services\CurrencyFormatter::formatNumber($totalBudgeted, $userCurrency) }}</span>
                <span class="text-sm text-muted">{{ $userCurrency }}</span>
            </span>
        </div>
        <div class="totals-item">
            <span class="totals-label">Spent in Budgeted</span>
            <span class="totals-value totals-expense">
                {{ \App\Services\CurrencyFormatter::formatNumber($totalSpentInBudgeted, $userCurrency) }}
                <span class="text-sm text-muted">{{ $userCurrency }}</span>
            </span>
        </div>
        <div class="totals-item">
            <span class="totals-label">Total Remaining</span>
            <span class="totals-value {{ bccomp($totalRemaining, '0', 2) >= 0 ? 'totals-income' : 'totals-expense' }}">
                {{ \App\Services\CurrencyFormatter::formatNumber($totalRemaining, $userCurrency) }}
                <span class="text-sm text-muted">{{ $userCurrency }}</span>
            </span>
        </div>
    </div>

    @if (! $hasExpenseCategories)
        <div class="card" style="padding: var(--space-8); text-align: center;">
            <h2 class="section-title" style="margin-bottom: var(--space-2);">No expense categories found</h2>
            <p class="text-muted" style="margin-bottom: var(--space-4);">You need expense categories before you can set spending limits.</p>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">Add an expense category</a>
        </div>
    @else
        @if (! $hasAnyBudgets)
            <div class="card" style="padding: var(--space-4); margin-bottom: var(--space-6); background-color: var(--color-surface-alt);">
                <p class="text-sm text-muted">
                    No budgets set for this month. Enter a limit next to a category below.
                </p>
            </div>
        @endif

        <div class="card" style="padding: 0; overflow: hidden;">
            <form method="POST" action="{{ route('budgets.update') }}" data-budget-form data-currency="{{ $userCurrency }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="month" value="{{ $monthRange->currentMonth }}">

                <div role="list">
                    @foreach ($categoryBudgets as $cat)
                        <div class="budget-row" role="listitem">
                            {{-- Category info --}}
                            <div class="budget-row-info">
                                <span class="category-color-dot" style="background-color: {{ $cat['color'] }};" aria-hidden="true"></span>
                                <div>
                                    <span style="font-weight: 600; color: var(--color-text);">{{ $cat['name'] }}</span>
                                    @if ($cat['is_archived'])
                                        <span style="margin-left: var(--space-1);">
                                            <x-badge text="Archived" variant="neutral" />
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Progress bar and status --}}
                            <div>
                                @if ($cat['amount_limit'] !== null && $cat['status'] !== null)
                                    <x-budget-bar
                                        :percent="$cat['percent'] ?? 0"
                                        :status="$cat['status']->value"
                                        :status-text="$cat['status']->label()"
                                        :spent="\App\Services\CurrencyFormatter::formatNumber($cat['spent'], $userCurrency)"
                                        :limit="\App\Services\CurrencyFormatter::formatNumber($cat['amount_limit'], $userCurrency)"
                                    />
                                    <div style="margin-top: 4px; font-size: 0.75rem; color: var(--color-text-muted);">
                                        Remaining: {{ \App\Services\CurrencyFormatter::formatNumber($cat['remaining'] ?? '0.00', $userCurrency) }} {{ $userCurrency }}
                                    </div>
                                @else
                                    <div style="font-size: 0.8125rem; color: var(--color-text-muted);">
                                        Spent: {{ \App\Services\CurrencyFormatter::formatNumber($cat['spent'], $userCurrency) }} {{ $userCurrency }} (No budget set)
                                    </div>
                                @endif
                            </div>

                            {{-- Limit input field --}}
                            <div class="budget-row-input">
                                <label for="limit-{{ $cat['id'] }}" class="sr-only">Budget limit for {{ $cat['name'] }}</label>
                                <div style="display: flex; align-items: center; gap: var(--space-2);">
                                    <input
                                        type="text"
                                        id="limit-{{ $cat['id'] }}"
                                        name="limits[{{ $cat['id'] }}]"
                                        value="{{ old('limits.' . $cat['id'], $cat['amount_limit']) }}"
                                        class="field-input"
                                        style="text-align: right; font-variant-numeric: tabular-nums;"
                                        placeholder="No limit"
                                        inputmode="decimal"
                                        data-budget-limit-input
                                    >
                                    <span class="text-sm text-muted" style="min-width: 28px;">{{ $userCurrency }}</span>
                                </div>
                                @error('limits.' . $cat['id'])
                                    <span class="field-error-text" style="display: block; font-size: 0.75rem; margin-top: 2px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="budget-sticky-save" style="padding: var(--space-4);">
                    <x-button variant="primary" type="submit">
                        Save budgets
                    </x-button>
                </div>
            </form>
        </div>
    @endif
</x-app-layout>
