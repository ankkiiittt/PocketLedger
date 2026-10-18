<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BudgetStatusService;
use App\Services\MonthRange;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        public ReportService $reportService,
        public BudgetStatusService $budgetStatusService
    ) {}

    /**
     * Display the monthly dashboard.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $monthParam = $request->query('month');
        $monthRange = MonthRange::fromString(is_string($monthParam) ? $monthParam : null);
        $start = $monthRange->startDateString();
        $nextStart = $monthRange->nextStartDateString();

        // 1. Month Totals
        $totals = $this->reportService->getMonthTotals($user, $monthRange);

        // 2. Budget used
        $budgetUsed = $this->reportService->getBudgetUsed($user, $monthRange);

        // 3. Top 5 budgets by percent used
        $budgetsQuery = DB::select(
            "SELECT c.id, c.name, c.color, b.amount_limit, COALESCE(SUM(t.amount), 0) AS spent
             FROM budgets b
             JOIN categories c ON c.id = b.category_id
             LEFT JOIN transactions t
               ON t.category_id = b.category_id AND t.user_id = b.user_id
               AND t.transaction_date >= :start AND t.transaction_date < :next_start
             WHERE b.user_id = :user AND b.month = :budget_month AND c.type = 'expense'
             GROUP BY c.id, c.name, c.color, b.id, b.amount_limit",
            [
                'user' => $user->id,
                'start' => $start,
                'next_start' => $nextStart,
                'budget_month' => $start,
            ]
        );

        $budgetsWithPercent = [];
        foreach ($budgetsQuery as $b) {
            $limit = (string) $b->amount_limit;
            $spent = (string) $b->spent;
            $percent = $this->budgetStatusService->getPercent($spent, $limit);
            $status = $this->budgetStatusService->getStatus($spent, $limit);
            $remaining = $this->budgetStatusService->getRemaining($spent, $limit);

            $budgetsWithPercent[] = [
                'id' => (int) $b->id,
                'name' => (string) $b->name,
                'color' => (string) $b->color,
                'limit' => $limit,
                'spent' => $spent,
                'percent' => $percent,
                'status' => $status,
                'remaining' => $remaining,
            ];
        }

        // Sort descending by percent used and take top 5
        usort($budgetsWithPercent, fn ($a, $b) => $b['percent'] <=> $a['percent']);
        $topBudgets = array_slice($budgetsWithPercent, 0, 5);

        // 4. 8 most recent transactions in the month
        $recentTransactions = $user->transactions()
            ->with(['category', 'recurringTransaction'])
            ->where('transaction_date', '>=', $start)
            ->where('transaction_date', '<', $nextStart)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        // Check if anything is recorded in this month
        $hasTransactionsInMonth = $user->transactions()
            ->where('transaction_date', '>=', $start)
            ->where('transaction_date', '<', $nextStart)
            ->exists();

        $hasBudgetsSet = count($budgetsQuery) > 0;

        return view('dashboard', [
            'monthRange' => $monthRange,
            'totals' => $totals,
            'budgetUsed' => $budgetUsed,
            'topBudgets' => $topBudgets,
            'recentTransactions' => $recentTransactions,
            'hasTransactionsInMonth' => $hasTransactionsInMonth,
            'hasBudgetsSet' => $hasBudgetsSet,
            'userCurrency' => $user->currency_code,
        ]);
    }
}
