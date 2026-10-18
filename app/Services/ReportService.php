<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function __construct(
        public BudgetStatusService $budgetStatusService
    ) {}

    /**
     * Get income, expense, net totals and savings rate for a given month.
     *
     * @return array{income: string, expense: string, net: string, savingsRate: string|null}
     */
    public function getMonthTotals(User $user, MonthRange $monthRange): array
    {
        $start = $monthRange->startDateString();
        $nextStart = $monthRange->nextStartDateString();

        $rows = DB::select(
            'SELECT c.type, SUM(t.amount) AS total
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user
               AND t.transaction_date >= :start AND t.transaction_date < :next_start
             GROUP BY c.type',
            [
                'user' => $user->id,
                'start' => $start,
                'next_start' => $nextStart,
            ]
        );

        $totals = ['income' => '0.00', 'expense' => '0.00'];
        foreach ($rows as $row) {
            $typeStr = is_string($row->type) ? $row->type : (string) $row->type;
            $totals[$typeStr] = (string) $row->total;
        }

        $income = $totals['income'];
        $expense = $totals['expense'];
        $net = bcsub($income, $expense, 2);

        // Savings rate = net divided by income as a percentage. Dash (null) when income is zero.
        $savingsRate = null;
        if (bccomp($income, '0', 2) > 0) {
            $ratio = bcdiv($net, $income, 6);
            $pct = bcmul($ratio, '100', 1);
            $savingsRate = number_format((float) $pct, 1, '.', '');
        }

        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $net,
            'savingsRate' => $savingsRate,
        ];
    }

    /**
     * Calculate budget used metric: expenses in budgeted categories divided by total budget limits.
     *
     * @return array{spentInBudgeted: string, totalLimit: string, percent: float|null}
     */
    public function getBudgetUsed(User $user, MonthRange $monthRange): array
    {
        $start = $monthRange->startDateString();
        $nextStart = $monthRange->nextStartDateString();

        $rows = DB::select(
            "SELECT b.amount_limit, COALESCE(SUM(t.amount), 0) AS spent
             FROM budgets b
             JOIN categories c ON c.id = b.category_id
             LEFT JOIN transactions t
               ON t.category_id = b.category_id AND t.user_id = b.user_id
               AND t.transaction_date >= :start AND t.transaction_date < :next_start
             WHERE b.user_id = :user AND b.month = :budget_month AND c.type = 'expense'
             GROUP BY b.id, b.amount_limit",
            [
                'user' => $user->id,
                'start' => $start,
                'next_start' => $nextStart,
                'budget_month' => $start,
            ]
        );

        $totalLimit = '0.00';
        $totalSpent = '0.00';

        foreach ($rows as $row) {
            $totalLimit = bcadd($totalLimit, (string) $row->amount_limit, 2);
            $totalSpent = bcadd($totalSpent, (string) $row->spent, 2);
        }

        $percent = null;
        if (bccomp($totalLimit, '0', 2) > 0) {
            $ratio = bcdiv($totalSpent, $totalLimit, 6);
            $percent = round((float) bcmul($ratio, '100', 4), 1);
        }

        return [
            'spentInBudgeted' => $totalSpent,
            'totalLimit' => $totalLimit,
            'percent' => $percent,
        ];
    }

    /**
     * Get expense category breakdown with budget variance for Reports and JSON endpoint.
     *
     * @return array{total: string, items: list<array{category_id: int, name: string, color: string, amount: string, percent: float, limit: string|null, variance: string|null}>}
     */
    public function getCategoryBreakdown(User $user, MonthRange $monthRange): array
    {
        $start = $monthRange->startDateString();
        $nextStart = $monthRange->nextStartDateString();

        $rows = DB::select(
            "SELECT c.id, c.name, c.color, b.amount_limit, COALESCE(SUM(t.amount), 0) AS spent
             FROM categories c
             LEFT JOIN transactions t
               ON t.category_id = c.id AND t.user_id = c.user_id
               AND t.transaction_date >= :start AND t.transaction_date < :next_start
             LEFT JOIN budgets b
               ON b.category_id = c.id AND b.user_id = c.user_id AND b.month = :budget_month
             WHERE c.user_id = :user AND c.type = 'expense'
               AND (c.is_archived = 0 OR t.id IS NOT NULL OR b.id IS NOT NULL)
             GROUP BY c.id, c.name, c.color, b.amount_limit
             ORDER BY spent DESC, c.name ASC",
            [
                'start' => $start,
                'next_start' => $nextStart,
                'budget_month' => $start,
                'user' => $user->id,
            ]
        );

        $totalSpent = '0.00';
        foreach ($rows as $row) {
            $totalSpent = bcadd($totalSpent, (string) $row->spent, 2);
        }

        $items = [];
        foreach ($rows as $row) {
            $spent = (string) $row->spent;
            if (bccomp($spent, '0', 2) <= 0) {
                continue; // Only categories with spending are in breakdown
            }

            $percent = 0.0;
            if (bccomp($totalSpent, '0', 2) > 0) {
                $ratio = bcdiv($spent, $totalSpent, 6);
                $percent = round((float) bcmul($ratio, '100', 4), 1);
            }

            $limit = $row->amount_limit !== null ? (string) $row->amount_limit : null;
            $variance = $limit !== null ? bcsub($limit, $spent, 2) : null;

            $items[] = [
                'category_id' => (int) $row->id,
                'name' => (string) $row->name,
                'color' => (string) $row->color,
                'amount' => $spent,
                'percent' => $percent,
                'limit' => $limit,
                'variance' => $variance,
            ];
        }

        return [
            'total' => $totalSpent,
            'items' => $items,
        ];
    }

    /**
     * Get 6-month income versus expense trend for Reports and JSON endpoint.
     * Always returns exactly 6 months ending with the selected month, oldest first.
     *
     * @return list<array{month: string, income: string, expense: string}>
     */
    public function getMonthlyTrend(User $user, MonthRange $monthRange): array
    {
        // 6 months ending with selected month
        $end = $monthRange->nextStart->copy();
        $start = $monthRange->start->copy()->subMonths(5)->startOfMonth();

        $rows = DB::select(
            "SELECT DATE_FORMAT(t.transaction_date, '%Y-%m') AS ym, c.type, SUM(t.amount) AS total
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user
               AND t.transaction_date >= :range_start AND t.transaction_date < :range_end
             GROUP BY ym, c.type
             ORDER BY ym",
            [
                'user' => $user->id,
                'range_start' => $start->toDateString(),
                'range_end' => $end->toDateString(),
            ]
        );

        $lookup = [];
        foreach ($rows as $row) {
            $ym = (string) $row->ym;
            $type = (string) $row->type;
            $lookup[$ym][$type] = (string) $row->total;
        }

        $result = [];
        $cursor = $start->copy();
        for ($i = 0; $i < 6; $i++) {
            $ym = $cursor->format('Y-m');
            $result[] = [
                'month' => $ym,
                'income' => $lookup[$ym]['income'] ?? '0.00',
                'expense' => $lookup[$ym]['expense'] ?? '0.00',
            ];
            $cursor->addMonthNoOverflow();
        }

        return $result;
    }

    /**
     * Get top 5 largest expenses for the given month.
     *
     * @return list<object{id: int, amount: string, transaction_date: string, description: string|null, category_name: string, category_color: string}>
     */
    public function getTopExpenses(User $user, MonthRange $monthRange, int $limit = 5): array
    {
        $start = $monthRange->startDateString();
        $nextStart = $monthRange->nextStartDateString();

        /** @var list<object{id: int, amount: string, transaction_date: string, description: string|null, category_name: string, category_color: string}> $expenses */
        $expenses = DB::select(
            "SELECT t.id, t.amount, t.transaction_date, t.description, c.name AS category_name, c.color AS category_color
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user AND c.type = 'expense'
               AND t.transaction_date >= :start AND t.transaction_date < :next_start
             ORDER BY t.amount DESC, t.transaction_date DESC, t.id DESC
             LIMIT :limit",
            [
                'user' => $user->id,
                'start' => $start,
                'next_start' => $nextStart,
                'limit' => $limit,
            ]
        );

        return $expenses;
    }

    /**
     * Stream CSV export of transactions for date range with BOM and formula injection protection.
     */
    public function exportTransactionsCsv(User $user, string $from, string $to): StreamedResponse
    {
        $filename = "pocketledger-transactions-{$from}-to-{$to}.csv";

        $response = new StreamedResponse(function () use ($user, $from, $to): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($handle, ['date', 'type', 'category', 'amount', 'description']);

            $query = $user->transactions()
                ->with('category')
                ->where('transaction_date', '>=', $from)
                ->where('transaction_date', '<=', $to)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc');

            $query->chunk(500, function ($transactions) use ($handle): void {
                foreach ($transactions as $t) {
                    $date = $t->transaction_date->format('Y-m-d');
                    $type = $t->category->type->value;
                    $category = $t->category->name;
                    $amount = (string) $t->amount;
                    $description = (string) ($t->description ?? '');

                    fputcsv($handle, [
                        self::sanitizeCsvCell($date),
                        self::sanitizeCsvCell($type),
                        self::sanitizeCsvCell($category),
                        self::sanitizeCsvCell($amount),
                        self::sanitizeCsvCell($description),
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'no-store, no-cache');

        return $response;
    }

    /**
     * Prevent CSV formula injection by prefixing cells starting with =, +, -, @, \t, or \r with an apostrophe.
     */
    public static function sanitizeCsvCell(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $firstChar = $value[0];
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
