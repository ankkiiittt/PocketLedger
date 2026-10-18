<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionProcessor
{
    /**
     * Process all due recurring transactions across all users (used by daily command).
     */
    public function processAllDue(?Carbon $today = null): int
    {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $totalCreated = 0;

        RecurringTransaction::where('is_active', true)
            ->whereNotNull('next_run_date')
            ->where('next_run_date', '<=', $today->toDateString())
            ->chunkById(200, function ($rules) use ($today, &$totalCreated): void {
                foreach ($rules as $rule) {
                    $totalCreated += $this->processRule($rule, $today);
                }
            });

        return $totalCreated;
    }

    /**
     * Process due recurring transactions for a specific user ("Process due now").
     */
    public function processForUser(User $user, ?Carbon $today = null): int
    {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $totalCreated = 0;

        $rules = $user->recurringTransactions()
            ->where('is_active', true)
            ->whereNotNull('next_run_date')
            ->where('next_run_date', '<=', $today->toDateString())
            ->get();

        foreach ($rules as $rule) {
            $totalCreated += $this->processRule($rule, $today);
        }

        return $totalCreated;
    }

    /**
     * Process a single recurring transaction rule idempotently with catch-up.
     */
    public function processRule(RecurringTransaction $rule, Carbon $today): int
    {
        return DB::transaction(function () use ($rule, $today) {
            /** @var RecurringTransaction|null $r */
            $r = RecurringTransaction::lockForUpdate()->find($rule->id);

            if (! $r || ! $r->is_active || ! $r->next_run_date) {
                return 0;
            }

            $guard = 0;
            $createdCount = 0;
            $todayDate = $today->toDateString();

            while ($guard++ < 400) {
                $runDate = $r->next_run_date->toDateString();
                if ($runDate > $todayDate) {
                    break;
                }

                if ($r->end_date && $runDate > $r->end_date->toDateString()) {
                    break;
                }

                // Insert idempotently using MySQL INSERT IGNORE backed by UNIQUE(recurring_transaction_id, transaction_date)
                $inserted = DB::table('transactions')->insertOrIgnore([
                    'user_id' => $r->user_id,
                    'category_id' => $r->category_id,
                    'amount' => $r->amount,
                    'description' => $r->description,
                    'transaction_date' => $runDate,
                    'recurring_transaction_id' => $r->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($inserted > 0) {
                    $createdCount++;
                }

                $r->runs_count++;
                $r->last_run_date = $r->next_run_date;
                $r->next_run_date = RecurrenceCalculator::occurrence($r->start_date, $r->frequency, $r->runs_count);
            }

            if ($r->end_date !== null && $r->next_run_date->toDateString() > $r->end_date->toDateString()) {
                $r->next_run_date = null; // ended
            }

            $r->save();

            return $createdCount;
        });
    }
}
