<x-app-layout title="Recurring transactions">
    <div class="recurring-toolbar">
        <div>
            <h1 class="page-title">Recurring transactions</h1>
            <p class="section-subtitle">Rules that create transactions automatically on a schedule.</p>
        </div>

        <div class="recurring-toolbar-actions">
            <form method="POST" action="{{ route('recurring.process') }}">
                @csrf
                <x-button variant="secondary" type="submit">
                    <x-icon name="refresh" size="sm" />
                    <span>Process due now</span>
                </x-button>
            </form>

            <a href="{{ route('recurring.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="sm" />
                <span>Add recurring transaction</span>
            </a>
        </div>
    </div>

    @if ($rules->isEmpty())
        <x-empty-state
            title="No recurring transactions"
            message="No recurring transactions. Add one for rent, salary or subscriptions."
            action-url="{{ route('recurring.create') }}"
            action-text="Add recurring transaction"
        />
    @else
        <div class="ledger-table-wrap">
            <table class="ledger-table responsive-stack-table">
                <thead>
                    <tr>
                        <th scope="col">Description</th>
                        <th scope="col">Category</th>
                        <th scope="col" class="th-right">Amount</th>
                        <th scope="col">Frequency</th>
                        <th scope="col">Next run</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="th-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rules as $rule)
                        @php
                            $isExpense = $rule->category->type->value === 'expense';
                            $status = $rule->status();
                        @endphp
                        <tr>
                            <td>
                                <div class="stack-row-top">
                                    <span style="font-weight: 500;">{{ $rule->description ?: $rule->category->name }}</span>
                                    <span class="desktop-hidden {{ $isExpense ? 'amount-expense' : 'amount-income' }}">
                                        {{ $isExpense ? '-' : '+' }}{{ \App\Services\CurrencyFormatter::formatNumber($rule->amount, $userCurrency) }} {{ $userCurrency }}
                                    </span>
                                </div>
                                <div class="stack-row-bottom desktop-hidden">
                                    <span class="category-badge-item">
                                        <span class="color-dot" style="background-color: {{ $rule->category->color }};"></span>
                                        <span>{{ $rule->category->name }}</span>
                                    </span>
                                    <span>{{ $rule->frequency->label() }}</span>
                                </div>
                            </td>

                            <td class="mobile-hidden">
                                <span class="category-badge-item">
                                    <span class="color-dot" style="background-color: {{ $rule->category->color }};"></span>
                                    <span>{{ $rule->category->name }}</span>
                                </span>
                            </td>

                            <td class="td-right mobile-hidden">
                                <span class="{{ $isExpense ? 'amount-expense' : 'amount-income' }}">
                                    {{ $isExpense ? '-' : '+' }}{{ \App\Services\CurrencyFormatter::formatNumber($rule->amount, $userCurrency) }}
                                </span>
                                <span class="text-sm text-muted">{{ $userCurrency }}</span>
                            </td>

                            <td class="mobile-hidden">
                                {{ $rule->frequency->label() }}
                            </td>

                            <td>
                                @if ($rule->next_run_date)
                                    <span>{{ $rule->next_run_date->format('d M Y') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @if ($status === 'Active')
                                    <span class="badge badge-active">Active</span>
                                @elseif ($status === 'Paused')
                                    <span class="badge badge-paused">Paused</span>
                                @else
                                    <span class="badge badge-ended">Ended</span>
                                @endif
                            </td>

                            <td class="td-right">
                                <div class="recurring-actions-cell">
                                    <a href="{{ route('recurring.edit', $rule) }}" class="btn btn-ghost btn-sm">Edit</a>

                                    @if ($status !== 'Ended')
                                        <form method="POST" action="{{ route('recurring.toggle', $rule) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-ghost btn-sm">
                                                {{ $rule->is_active ? 'Pause' : 'Resume' }}
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('recurring.destroy', $rule) }}"
                                        data-confirm="Are you sure you want to delete this recurring rule? Existing transactions will be kept."
                                        style="display: inline;"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
