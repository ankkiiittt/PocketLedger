<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBudgetsRequest;
use App\Models\User;
use App\Services\BudgetStatusService;
use App\Services\MonthRange;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        public BudgetStatusService $budgetStatusService
    ) {}

    /**
     * Display a listing of monthly category budgets with spending progress.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $monthParam = $request->query('month');
        $monthRange = MonthRange::fromString(is_string($monthParam) ? $monthParam : null);
        $start = $monthRange->startDateString();
        $nextStart = $monthRange->nextStartDateString();

        $hasExpenseCategories = $user->categories()->expense()->exists();

        // Query according to Backend Schema section 6 contract
        /** @var list<object{id: int|string, name: string, color: string, is_archived: int|string, budget_id: int|string|null, amount_limit: string|null, spent: string|float}> $rawCategories */
        $rawCategories = DB::select(
            "SELECT c.id, c.name, c.color, c.is_archived, b.id AS budget_id, b.amount_limit, COALESCE(SUM(t.amount), 0) AS spent
             FROM categories c
             LEFT JOIN transactions t
               ON t.category_id = c.id AND t.user_id = c.user_id
               AND t.transaction_date >= :start AND t.transaction_date < :next_start
             LEFT JOIN budgets b
               ON b.category_id = c.id AND b.user_id = c.user_id AND b.month = :budget_month
             WHERE c.user_id = :user_id AND c.type = 'expense'
               AND (c.is_archived = 0 OR t.id IS NOT NULL OR b.id IS NOT NULL)
             GROUP BY c.id, c.name, c.color, c.is_archived, b.id, b.amount_limit
             ORDER BY spent DESC, c.name ASC",
            [
                'start' => $start,
                'next_start' => $nextStart,
                'budget_month' => $start,
                'user_id' => $user->id,
            ]
        );

        $totalBudgeted = '0.00';
        $totalSpentInBudgeted = '0.00';
        $hasAnyBudgets = false;

        $categoryBudgets = [];
        foreach ($rawCategories as $cat) {
            $limit = $cat->amount_limit !== null ? (string) $cat->amount_limit : null;
            $spent = (string) $cat->spent;

            if ($limit !== null) {
                $hasAnyBudgets = true;
                $totalBudgeted = bcadd($totalBudgeted, $limit, 2);
                $totalSpentInBudgeted = bcadd($totalSpentInBudgeted, $spent, 2);

                $status = $this->budgetStatusService->getStatus($spent, $limit);
                $remaining = $this->budgetStatusService->getRemaining($spent, $limit);
                $percent = $this->budgetStatusService->getPercent($spent, $limit);
            } else {
                $status = null;
                $remaining = null;
                $percent = null;
            }

            $categoryBudgets[] = [
                'id' => (int) $cat->id,
                'name' => (string) $cat->name,
                'color' => (string) $cat->color,
                'is_archived' => (bool) $cat->is_archived,
                'amount_limit' => $limit,
                'spent' => $spent,
                'status' => $status,
                'remaining' => $remaining,
                'percent' => $percent,
            ];
        }

        $totalRemaining = bcsub($totalBudgeted, $totalSpentInBudgeted, 2);

        return view('budgets.index', [
            'monthRange' => $monthRange,
            'categoryBudgets' => $categoryBudgets,
            'totalBudgeted' => $totalBudgeted,
            'totalSpentInBudgeted' => $totalSpentInBudgeted,
            'totalRemaining' => $totalRemaining,
            'hasAnyBudgets' => $hasAnyBudgets,
            'hasExpenseCategories' => $hasExpenseCategories,
            'userCurrency' => $user->currency_code,
        ]);
    }

    /**
     * Bulk save budget limits for a given month.
     */
    public function update(UpdateBudgetsRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $monthYm = $request->string('month')->value();
        $monthDate = Carbon::createFromFormat('Y-m', $monthYm)->startOfMonth()->toDateString();

        /** @var array<string, mixed> $limits */
        $limits = $request->input('limits', []);

        DB::transaction(function () use ($user, $monthDate, $limits) {
            foreach ($limits as $categoryId => $limitVal) {
                $catId = (int) $categoryId;
                $trimmed = is_string($limitVal) ? trim($limitVal) : (is_numeric($limitVal) ? (string) $limitVal : null);

                if ($trimmed === null || $trimmed === '') {
                    $user->budgets()
                        ->where('category_id', $catId)
                        ->where('month', $monthDate)
                        ->delete();
                } else {
                    $user->budgets()->updateOrCreate(
                        [
                            'category_id' => $catId,
                            'month' => $monthDate,
                        ],
                        [
                            'amount_limit' => $trimmed,
                        ]
                    );
                }
            }
        });

        return redirect()->route('budgets.index', ['month' => $monthYm])
            ->with('success', 'Budgets saved.');
    }

    /**
     * Copy budget limits from the previous month without overwriting existing ones.
     */
    public function copy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $monthYm = (string) $request->input('month', Carbon::now()->format('Y-m'));
        $monthRange = MonthRange::fromString($monthYm);
        $thisMonth = $monthRange->startDateString();
        $prevMonth = $monthRange->start->copy()->subMonthNoOverflow()->format('Y-m-d');

        // Execute SQL contract query from Backend Schema section 6
        $affected = DB::affectingStatement(
            'INSERT IGNORE INTO budgets (user_id, category_id, month, amount_limit, created_at, updated_at)
             SELECT b.user_id, b.category_id, ?, b.amount_limit, NOW(), NOW()
             FROM budgets b
             JOIN categories c ON c.id = b.category_id
             WHERE b.user_id = ? AND b.month = ? AND c.is_archived = 0',
            [$thisMonth, $user->id, $prevMonth]
        );

        if ($affected > 0) {
            $msg = sprintf('Copied %d %s.', $affected, Str::plural('budget', $affected));

            return redirect()->route('budgets.index', ['month' => $monthRange->currentMonth])
                ->with('success', $msg);
        }

        return redirect()->route('budgets.index', ['month' => $monthRange->currentMonth])
            ->with('info', 'Nothing to copy.');
    }
}
