<x-app-layout title="Reports">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--space-4); margin-bottom: var(--space-6);">
        <div>
            <h1 class="page-title">Financial reports</h1>
            <p class="section-subtitle">Detailed breakdown, multi-month trends and data export for {{ $monthRange->label }}.</p>
        </div>

        <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap;">
            <x-month-switcher :month-range="$monthRange" />
        </div>
    </div>

    {{-- Summary Strip --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-8);">
        <x-stat
            label="Total Income"
            :value="\App\Services\CurrencyFormatter::formatNumber($totals['income'], $userCurrency)"
            accent="income"
            subtext="{{ $userCurrency }}"
        />
        <x-stat
            label="Total Expenses"
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
            label="Savings Rate"
            :value="$totals['savingsRate'] !== null ? $totals['savingsRate'] . '%' : '-'"
            accent="neutral"
            subtext="of total income"
        />
    </div>

    @if (! $hasDataInMonth)
        <div class="card" style="padding: var(--space-8); text-align: center; margin-bottom: var(--space-8);">
            <h2 class="section-title" style="margin-bottom: var(--space-2);">No data for this month</h2>
            <p class="text-muted" style="margin-bottom: var(--space-4);">There are no transactions recorded in {{ $monthRange->label }}.</p>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">Add transaction</a>
        </div>
    @endif

    {{-- Category Breakdown and Donut Chart --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
        {{-- Donut Chart Card --}}
        <div class="chart-card" data-chart="donut" data-chart-url="{{ route('reports.data.category-breakdown', ['month' => $monthRange->currentMonth]) }}">
            <div class="chart-header">
                <h2 class="chart-title">Spending distribution</h2>
            </div>
            <div class="chart-container" style="min-height: 240px;">
                <div class="chart-loading-wrap" data-chart-loading>
                    <div class="chart-spinner" aria-hidden="true"></div>
                    <span>Loading chart...</span>
                </div>
                <div class="chart-error-wrap" data-chart-error hidden>
                    <p>Could not load chart.</p>
                    <button type="button" class="btn btn-secondary btn-sm" data-chart-retry>Retry</button>
                </div>
                <canvas data-chart-canvas role="img" aria-label="Spending distribution donut chart" style="max-height: 260px;" hidden></canvas>
            </div>
            <div data-donut-legend style="margin-top: var(--space-4);"></div>
        </div>

        {{-- Breakdown Table --}}
        <div class="card" style="padding: 0; overflow-x: auto;">
            <div style="padding: var(--space-4) var(--space-5); border-bottom: 1px solid var(--color-border);">
                <h2 class="section-title">Category breakdown</h2>
            </div>
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th scope="col" style="text-align: left; padding: var(--space-3) var(--space-4);">Category</th>
                        <th scope="col" style="text-align: right; padding: var(--space-3) var(--space-4);">Spent</th>
                        <th scope="col" style="text-align: right; padding: var(--space-3) var(--space-4);">Share</th>
                        <th scope="col" style="text-align: right; padding: var(--space-3) var(--space-4);">Budget</th>
                        <th scope="col" style="text-align: right; padding: var(--space-3) var(--space-4);">Variance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($breakdown['items'] as $item)
                        <tr class="table-row">
                            <td style="padding: var(--space-3) var(--space-4);">
                                <span style="display: inline-flex; align-items: center; gap: var(--space-2); font-weight: 500;">
                                    <span class="category-color-dot" style="background-color: {{ $item['color'] }};" aria-hidden="true"></span>
                                    <span>{{ $item['name'] }}</span>
                                </span>
                            </td>
                            <td style="padding: var(--space-3) var(--space-4); text-align: right; font-variant-numeric: tabular-nums;">
                                {{ \App\Services\CurrencyFormatter::formatNumber($item['amount'], $userCurrency) }}
                            </td>
                            <td style="padding: var(--space-3) var(--space-4); text-align: right; font-variant-numeric: tabular-nums;" class="text-muted">
                                {{ $item['percent'] }}%
                            </td>
                            <td style="padding: var(--space-3) var(--space-4); text-align: right; font-variant-numeric: tabular-nums;" class="text-muted">
                                {{ $item['limit'] !== null ? \App\Services\CurrencyFormatter::formatNumber($item['limit'], $userCurrency) : '-' }}
                            </td>
                            <td style="padding: var(--space-3) var(--space-4); text-align: right; font-variant-numeric: tabular-nums;">
                                @if ($item['variance'] !== null)
                                    <span style="{{ bccomp($item['variance'], '0', 2) < 0 ? 'color: var(--color-expense); font-weight: 600;' : 'color: var(--color-income);' }}">
                                        {{ bccomp($item['variance'], '0', 2) >= 0 ? '+' : '' }}{{ \App\Services\CurrencyFormatter::formatNumber($item['variance'], $userCurrency) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: var(--space-6); text-align: center;" class="text-muted">
                                No expense spending recorded this month.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6-Month Trend Section with Bar Chart and Accessible Data Table --}}
    <div class="card" style="margin-bottom: var(--space-8); padding: var(--space-5);">
        <div style="margin-bottom: var(--space-4);">
            <h2 class="section-title">6-Month trend</h2>
            <p class="section-subtitle">Income versus expenses over the past 6 months.</p>
        </div>

        <div class="chart-card" data-chart="trend" data-chart-url="{{ route('reports.data.monthly-trend', ['month' => $monthRange->currentMonth]) }}" style="border: none; padding: 0; margin-bottom: var(--space-6);">
            <div class="chart-container" style="min-height: 260px;">
                <div class="chart-loading-wrap" data-chart-loading>
                    <div class="chart-spinner" aria-hidden="true"></div>
                    <span>Loading trend...</span>
                </div>
                <div class="chart-error-wrap" data-chart-error hidden>
                    <p>Could not load chart.</p>
                    <button type="button" class="btn btn-secondary btn-sm" data-chart-retry>Retry</button>
                </div>
                <canvas data-chart-canvas role="img" aria-label="6-month income versus expense trend chart" style="max-height: 300px;" hidden></canvas>
            </div>
        </div>

        {{-- Accessible Table Alternative for the Trend --}}
        <div style="overflow-x: auto; border-top: 1px solid var(--color-border); padding-top: var(--space-4);">
            <h3 class="text-sm" style="font-weight: 600; margin-bottom: var(--space-2); color: var(--color-text-muted);">Trend summary table</h3>
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th scope="col" style="text-align: left; padding: var(--space-2) var(--space-3);">Month</th>
                        <th scope="col" style="text-align: right; padding: var(--space-2) var(--space-3);">Income ({{ $userCurrency }})</th>
                        <th scope="col" style="text-align: right; padding: var(--space-2) var(--space-3);">Expenses ({{ $userCurrency }})</th>
                        <th scope="col" style="text-align: right; padding: var(--space-2) var(--space-3);">Net ({{ $userCurrency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthlyTrend as $trendItem)
                        @php
                            $mNet = bcsub($trendItem['income'], $trendItem['expense'], 2);
                        @endphp
                        <tr class="table-row">
                            <td style="padding: var(--space-2) var(--space-3);">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $trendItem['month'])->format('F Y') }}
                            </td>
                            <td style="padding: var(--space-2) var(--space-3); text-align: right; font-variant-numeric: tabular-nums; color: var(--color-income);">
                                {{ \App\Services\CurrencyFormatter::formatNumber($trendItem['income'], $userCurrency) }}
                            </td>
                            <td style="padding: var(--space-2) var(--space-3); text-align: right; font-variant-numeric: tabular-nums; color: var(--color-expense);">
                                {{ \App\Services\CurrencyFormatter::formatNumber($trendItem['expense'], $userCurrency) }}
                            </td>
                            <td style="padding: var(--space-2) var(--space-3); text-align: right; font-variant-numeric: tabular-nums; font-weight: 500;">
                                {{ bccomp($mNet, '0', 2) >= 0 ? '+' : '' }}{{ \App\Services\CurrencyFormatter::formatNumber($mNet, $userCurrency) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top 5 Largest Expenses and Export Block --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-6);">
        {{-- Top 5 Largest Expenses --}}
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: var(--space-4) var(--space-5); border-bottom: 1px solid var(--color-border);">
                <h2 class="section-title">Top 5 largest expenses</h2>
            </div>
            @if (empty($topExpenses))
                <div style="padding: var(--space-6); text-align: center;" class="text-muted">
                    No expense transactions for this month.
                </div>
            @else
                <div role="list">
                    @foreach ($topExpenses as $exp)
                        <div class="category-row-item" role="listitem" style="padding: var(--space-3) var(--space-5);">
                            <div class="category-info">
                                <span class="category-color-dot" style="background-color: {{ $exp->category_color }};" aria-hidden="true"></span>
                                <div>
                                    <span style="font-weight: 500;">{{ $exp->description ?: $exp->category_name }}</span>
                                    <div class="text-xs text-muted" style="margin-top: 2px;">
                                        {{ \Carbon\Carbon::parse($exp->transaction_date)->format('d M Y') }} • {{ $exp->category_name }}
                                    </div>
                                </div>
                            </div>
                            <span style="font-weight: 600; color: var(--color-expense); font-variant-numeric: tabular-nums;">
                                -{{ \App\Services\CurrencyFormatter::formatNumber($exp->amount, $userCurrency) }}
                                <span class="text-xs text-muted">{{ $userCurrency }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- CSV Export Block --}}
        <div class="card" style="padding: var(--space-5);">
            <div style="margin-bottom: var(--space-4);">
                <h2 class="section-title">Export transactions</h2>
                <p class="section-subtitle">Download transaction data in CSV format (up to 5 years).</p>
            </div>

            <form method="GET" action="{{ route('reports.export') }}">
                <x-field name="from" label="From date" required="true">
                    <input
                        type="date"
                        name="from"
                        id="export-from"
                        class="field-input"
                        value="{{ old('from', $defaultFrom) }}"
                        required
                    >
                </x-field>

                <x-field name="to" label="To date" required="true">
                    <input
                        type="date"
                        name="to"
                        id="export-to"
                        class="field-input"
                        value="{{ old('to', $defaultTo) }}"
                        required
                    >
                </x-field>

                <div style="margin-top: var(--space-6);">
                    <x-button variant="primary" type="submit" style="width: 100%;">
                        <x-icon name="download" size="sm" />
                        <span>Download CSV</span>
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
