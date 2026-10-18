<x-app-layout title="Dashboard">
    {{-- Month switcher and Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--space-4); margin-bottom: var(--space-6);">
        <div>
            <h1 class="page-title">Monthly overview</h1>
            <p class="section-subtitle">Financial summary for {{ $monthRange->label }}.</p>
        </div>

        <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap;">
            <x-month-switcher :month-range="$monthRange" />
            <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="sm" />
                <span>Add transaction</span>
            </a>
        </div>
    </div>

    {{-- Four Summary Tiles --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-8);">
        <x-stat
            label="Income"
            :value="\App\Services\CurrencyFormatter::formatNumber($totals['income'], $userCurrency)"
            accent="income"
            subtext="{{ $userCurrency }}"
        />
        <x-stat
            label="Expenses"
            :value="\App\Services\CurrencyFormatter::formatNumber($totals['expense'], $userCurrency)"
            accent="expense"
            subtext="{{ $userCurrency }}"
        />
        <x-stat
            label="Net Balance"
            :value="(bccomp($totals['net'], '0', 2) >= 0 ? '+' : '') . \App\Services\CurrencyFormatter::formatNumber($totals['net'], $userCurrency)"
            :accent="bccomp($totals['net'], '0', 2) >= 0 ? 'income' : 'expense'"
            subtext="{{ $userCurrency }}"
        />
        <x-stat
            label="Budget Used"
            :value="$budgetUsed['percent'] !== null ? round($budgetUsed['percent']) . '%' : '-'"
            accent="neutral"
            subtext="of active budgets"
        />
    </div>

    {{-- Two Columns: Donut Chart (Left) and Top 5 Budgets (Right) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
        {{-- Donut Chart Card --}}
        <div class="chart-card" data-chart="donut" data-chart-url="{{ route('reports.data.category-breakdown', ['month' => $monthRange->currentMonth]) }}">
            <div class="chart-header">
                <h2 class="chart-title">Spending by category</h2>
            </div>

            <div class="chart-container" style="min-height: 220px;">
                <div class="chart-loading-wrap" data-chart-loading>
                    <div class="chart-spinner" aria-hidden="true"></div>
                    <span>Loading chart...</span>
                </div>

                <div class="chart-error-wrap" data-chart-error hidden>
                    <p>Could not load chart.</p>
                    <button type="button" class="btn btn-secondary btn-sm" data-chart-retry>Retry</button>
                </div>

                <canvas
                    data-chart-canvas
                    role="img"
                    aria-label="Spending by category donut chart for {{ $monthRange->label }}"
                    style="max-height: 240px;"
                    hidden
                ></canvas>
            </div>

            {{-- Accessible Legend / Text alternative --}}
            <div data-donut-legend style="margin-top: var(--space-4);"></div>
        </div>

        {{-- Top 5 Budgets --}}
        <div class="card" style="padding: var(--space-5); display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
                <h2 class="section-title">Budget progress</h2>
                <a href="{{ route('budgets.index', ['month' => $monthRange->currentMonth]) }}" class="btn btn-secondary btn-sm">
                    Manage budgets
                </a>
            </div>

            @if (empty($topBudgets))
                <div style="padding: var(--space-6); text-align: center; margin: auto 0;">
                    <p class="text-muted" style="margin-bottom: var(--space-3);">No budgets set for this month.</p>
                    <a href="{{ route('budgets.index', ['month' => $monthRange->currentMonth]) }}" class="btn btn-secondary btn-sm">
                        Set a budget
                    </a>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                    @foreach ($topBudgets as $b)
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                <span style="display: inline-flex; align-items: center; gap: var(--space-2); font-weight: 500;">
                                    <span class="category-color-dot" style="background-color: {{ $b['color'] }};" aria-hidden="true"></span>
                                    <span>{{ $b['name'] }}</span>
                                </span>
                                <span class="text-xs text-muted tabular-nums">
                                    {{ \App\Services\CurrencyFormatter::formatNumber($b['spent'], $userCurrency) }} / {{ \App\Services\CurrencyFormatter::formatNumber($b['limit'], $userCurrency) }}
                                </span>
                            </div>
                            <x-budget-bar
                                :percent="$b['percent']"
                                :status="$b['status']->value"
                                :status-text="$b['status']->label()"
                            />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Transactions Section --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: var(--space-4) var(--space-5); border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between;">
            <h2 class="section-title">Recent transactions</h2>
            <a href="{{ route('transactions.index', ['from' => $monthRange->startDateString(), 'to' => $monthRange->start->copy()->endOfMonth()->toDateString()]) }}" class="btn btn-secondary btn-sm">
                View all
            </a>
        </div>

        @if ($recentTransactions->isEmpty())
            <div style="padding: var(--space-8); text-align: center;">
                <p class="text-muted" style="margin-bottom: var(--space-4);">Nothing recorded for this month yet.</p>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">Add transaction</a>
            </div>
        @else
            <div role="list">
                @foreach ($recentTransactions as $txn)
                    <div class="category-row-item" role="listitem" style="padding: var(--space-3) var(--space-5);">
                        <div class="category-info">
                            <span class="category-color-dot" style="background-color: {{ $txn->category->color }};" aria-hidden="true"></span>
                            <div>
                                <span style="font-weight: 500; color: var(--color-text);">
                                    {{ $txn->description ?: $txn->category->name }}
                                </span>
                                <div class="text-xs text-muted" style="margin-top: 2px;">
                                    <span>{{ $txn->transaction_date->format('d M Y') }}</span>
                                    <span style="margin: 0 4px;">•</span>
                                    <span>{{ $txn->category->name }}</span>
                                    @if ($txn->recurring_transaction_id)
                                        <span style="margin-left: 6px;"><x-badge text="Recurring" variant="neutral" /></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: var(--space-4);">
                            <span style="font-weight: 600; font-variant-numeric: tabular-nums; {{ $txn->category->type->value === 'expense' ? 'color: var(--color-expense);' : 'color: var(--color-income);' }}">
                                {{ $txn->category->type->value === 'expense' ? '-' : '+' }}{{ \App\Services\CurrencyFormatter::formatNumber($txn->amount, $userCurrency) }}
                                <span class="text-xs text-muted">{{ $userCurrency }}</span>
                            </span>
                            <a href="{{ route('transactions.edit', ['transaction' => $txn, 'return_to' => request()->getRequestUri()]) }}" class="btn btn-secondary btn-sm">
                                Edit
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
