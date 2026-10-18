<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BudgetStatus;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetStatusService
{
    /**
     * Compute budget status enum based on spent and limit amounts.
     *
     * Thresholds:
     * - under 80% is 'On track'
     * - 80% to 100% is 'Nearing limit'
     * - over 100% is 'Over budget'
     */
    public function getStatus(string $spent, string $limit): BudgetStatus
    {
        if (bccomp($limit, '0', 2) <= 0) {
            return BudgetStatus::OverBudget;
        }

        // Calculate percentage: (spent * 100) / limit with 4 scale
        $ratio = bcdiv($spent, $limit, 6);
        $percent = (float) bcmul($ratio, '100', 4);

        if ($percent < 80.0) {
            return BudgetStatus::OnTrack;
        }

        if ($percent <= 100.0) {
            return BudgetStatus::NearingLimit;
        }

        return BudgetStatus::OverBudget;
    }

    /**
     * Calculate remaining budget amount: limit - spent.
     */
    public function getRemaining(string $spent, string $limit): string
    {
        return bcsub($limit, $spent, 2);
    }

    /**
     * Calculate percentage used: (spent / limit) * 100.
     */
    public function getPercent(string $spent, string $limit): float
    {
        if (bccomp($limit, '0', 2) <= 0) {
            return 100.0;
        }

        $ratio = bcdiv($spent, $limit, 6);

        return (float) bcmul($ratio, '100', 2);
    }

    /**
     * Generate the saved-expense message when a budget exists for the transaction's category and month.
     * Format: "Saved. {category}: {spent} of {limit} used in {Mon YYYY} ({pct}%)."
     */
    public function getSavedExpenseMessage(User $user, Category $category, string $transactionDate): ?string
    {
        $date = Carbon::parse($transactionDate);
        $monthStart = $date->copy()->startOfMonth()->toDateString();
        $nextMonthStart = $date->copy()->startOfMonth()->addMonth()->toDateString();

        /** @var Budget|null $budget */
        $budget = $user->budgets()
            ->where('category_id', $category->id)
            ->where('month', $monthStart)
            ->first();

        if (! $budget) {
            return null;
        }

        $spentSum = (string) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->where('transaction_date', '>=', $monthStart)
            ->where('transaction_date', '<', $nextMonthStart)
            ->sum('amount');

        $limit = (string) $budget->amount_limit;
        $pct = (int) round($this->getPercent($spentSum, $limit));

        $formattedSpent = CurrencyFormatter::formatNumber($spentSum, $user->currency_code);
        $formattedLimit = CurrencyFormatter::formatNumber($limit, $user->currency_code);
        $monthLabel = $date->format('M Y');

        return sprintf(
            'Saved. %s: %s of %s used in %s (%d%%).',
            $category->name,
            $formattedSpent,
            $formattedLimit,
            $monthLabel,
            $pct
        );
    }
}
