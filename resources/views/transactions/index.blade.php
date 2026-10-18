<x-app-layout title="Transactions">
    <div class="transactions-toolbar">
        <div>
            <h1 class="page-title">Transactions</h1>
            <p class="section-subtitle">Track, filter and manage your income and expenses.</p>
        </div>

        <div>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="sm" />
                <span>Add transaction</span>
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('transactions.index') }}" class="filter-form">
            <div class="filter-group">
                <label for="filter-from">From</label>
                <input
                    type="date"
                    id="filter-from"
                    name="from"
                    class="field-input"
                    value="{{ $currentFrom }}"
                >
            </div>

            <div class="filter-group">
                <label for="filter-to">To</label>
                <input
                    type="date"
                    id="filter-to"
                    name="to"
                    class="field-input"
                    value="{{ $currentTo }}"
                >
            </div>

            <div class="filter-group">
                <label for="filter-type">Type</label>
                <select id="filter-type" name="type" class="field-input">
                    <option value="">All types</option>
                    <option value="expense" {{ $currentType === 'expense' ? 'selected' : '' }}>Expense</option>
                    <option value="income" {{ $currentType === 'income' ? 'selected' : '' }}>Income</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-category">Category</label>
                <select id="filter-category" name="category_id" class="field-input">
                    <option value="">All categories</option>
                    @foreach ($filterCategories as $cat)
                        <option value="{{ $cat->id }}" {{ $currentCategoryId === $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ ucfirst($cat->type->value) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group" style="flex: 2 1 180px;">
                <label for="filter-query">Search description</label>
                <input
                    type="text"
                    id="filter-query"
                    name="q"
                    class="field-input"
                    placeholder="Search text..."
                    value="{{ $currentQuery }}"
                    maxlength="100"
                >
            </div>

            <div class="filter-actions">
                <x-button variant="primary" type="submit">Apply</x-button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    {{-- Totals Strip --}}
    <div class="totals-strip">
        <div class="totals-item">
            <span class="totals-label">Total Income</span>
            <span class="totals-value totals-income">
                +{{ \App\Services\CurrencyFormatter::formatNumber($incomeTotal, $userCurrency) }}
                <span class="text-sm text-muted">{{ $userCurrency }}</span>
            </span>
        </div>
        <div class="totals-item">
            <span class="totals-label">Total Expenses</span>
            <span class="totals-value totals-expense">
                -{{ \App\Services\CurrencyFormatter::formatNumber($expenseTotal, $userCurrency) }}
                <span class="text-sm text-muted">{{ $userCurrency }}</span>
            </span>
        </div>
        <div class="totals-item">
            <span class="totals-label">Net Balance</span>
            <span class="totals-value {{ bccomp($netTotal, '0', 2) >= 0 ? 'totals-income' : 'totals-expense' }}">
                {{ bccomp($netTotal, '0', 2) >= 0 ? '+' : '' }}{{ \App\Services\CurrencyFormatter::formatNumber($netTotal, $userCurrency) }}
                <span class="text-sm text-muted">{{ $userCurrency }}</span>
            </span>
        </div>
    </div>

    {{-- Transaction List --}}
    @if ($transactions->isEmpty())
        @if ($hasAnyTransactions)
            <div class="card" style="padding: var(--space-8); text-align: center;">
                <p class="section-title" style="margin-bottom: var(--space-2);">No transactions match these filters.</p>
                <p class="text-muted" style="margin-bottom: var(--space-4);">Try broadening your dates or clearing your search term.</p>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Clear filters</a>
            </div>
        @else
            <x-empty-state
                title="No transactions yet"
                description="Add your first transaction to start tracking your expenses and income."
                action-label="Add transaction"
                :action-url="route('transactions.create')"
            />
        @endif
    @else
        <div class="card" style="padding: 0; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th scope="col" style="text-align: left; padding: var(--space-3) var(--space-4);">Date</th>
                        <th scope="col" style="text-align: left; padding: var(--space-3) var(--space-4);">Description</th>
                        <th scope="col" style="text-align: left; padding: var(--space-3) var(--space-4);">Category</th>
                        <th scope="col" style="text-align: right; padding: var(--space-3) var(--space-4);">Amount</th>
                        <th scope="col" style="text-align: right; padding: var(--space-3) var(--space-4);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr class="table-row">
                            <td style="padding: var(--space-3) var(--space-4); white-space: nowrap;" class="text-muted text-sm">
                                {{ $transaction->transaction_date->format('d M Y') }}
                            </td>
                            <td style="padding: var(--space-3) var(--space-4);">
                                <span style="font-weight: 500;">
                                    {{ $transaction->description ?: $transaction->category->name }}
                                </span>
                                @if ($transaction->recurring_transaction_id)
                                    <span style="margin-left: var(--space-2);">
                                        <x-badge text="Recurring" variant="neutral" />
                                    </span>
                                @endif
                            </td>
                            <td style="padding: var(--space-3) var(--space-4);">
                                <span style="display: inline-flex; align-items: center; gap: var(--space-2);">
                                    <span class="category-color-dot" style="background-color: {{ $transaction->category->color }};" aria-hidden="true"></span>
                                    <span>{{ $transaction->category->name }}</span>
                                </span>
                            </td>
                            <td style="padding: var(--space-3) var(--space-4); text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap;">
                                @if ($transaction->category->type->value === 'expense')
                                    <span class="sr-only">Expense: </span>
                                    <span style="color: var(--color-expense); font-weight: 600;">
                                        -{{ \App\Services\CurrencyFormatter::formatNumber($transaction->amount, $userCurrency) }}
                                    </span>
                                @else
                                    <span class="sr-only">Income: </span>
                                    <span style="color: var(--color-income); font-weight: 600;">
                                        +{{ \App\Services\CurrencyFormatter::formatNumber($transaction->amount, $userCurrency) }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: var(--space-3) var(--space-4); text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: var(--space-2);">
                                    <a href="{{ route('transactions.edit', ['transaction' => $transaction, 'return_to' => request()->getRequestUri()]) }}" class="btn btn-secondary btn-sm">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" style="display:inline;" data-confirm="Are you sure you want to delete this transaction?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: var(--space-6);">
            {{ $transactions->links() }}
        </div>
    @endif
</x-app-layout>
