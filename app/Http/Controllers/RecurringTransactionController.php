<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Frequency;
use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Services\RecurrenceCalculator;
use App\Services\RecurringTransactionProcessor;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    public function __construct(
        public RecurringTransactionProcessor $processor
    ) {}

    /**
     * Display a listing of recurring rules with status and next run.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $rules = $user->recurringTransactions()
            ->with('category')
            ->orderBy('is_active', 'desc')
            ->orderBy('next_run_date', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return view('recurring.index', [
            'rules' => $rules,
            'userCurrency' => $user->currency_code,
        ]);
    }

    /**
     * Show the form for creating a new recurring transaction rule.
     */
    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $categories = $user->categories()
            ->active()
            ->orderBy('name')
            ->get();

        return view('recurring.create', [
            'categories' => $categories,
            'frequencies' => Frequency::cases(),
            'userCurrency' => $user->currency_code,
            'today' => Carbon::today()->toDateString(),
        ]);
    }

    /**
     * Store a newly created recurring rule.
     */
    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $startDate = $request->string('start_date')->value();
        $endDate = $request->filled('end_date') ? $request->string('end_date')->value() : null;

        $user->recurringTransactions()->create([
            'category_id' => $request->integer('category_id'),
            'amount' => $request->string('amount')->value(),
            'description' => $request->filled('description') ? trim($request->string('description')->value()) : null,
            'frequency' => $request->enum('frequency', Frequency::class),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'next_run_date' => $startDate,
            'runs_count' => 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('recurring.index')->with('success', 'Recurring rule created.');
    }

    /**
     * Show the form for editing the recurring rule.
     */
    public function edit(Request $request, RecurringTransaction $recurringTransaction): View
    {
        $this->authorize('update', $recurringTransaction);

        /** @var User $user */
        $user = $request->user();

        $categories = $user->categories()
            ->where(function ($q) use ($recurringTransaction) {
                $q->active()->orWhere('id', $recurringTransaction->category_id);
            })
            ->orderBy('name')
            ->get();

        return view('recurring.edit', [
            'rule' => $recurringTransaction,
            'categories' => $categories,
            'frequencies' => Frequency::cases(),
            'userCurrency' => $user->currency_code,
        ]);
    }

    /**
     * Update the recurring rule (affects future occurrences only).
     */
    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $this->authorize('update', $recurringTransaction);

        $endDate = $request->filled('end_date') ? $request->string('end_date')->value() : null;

        $recurringTransaction->update([
            'category_id' => $request->integer('category_id'),
            'amount' => $request->string('amount')->value(),
            'description' => $request->filled('description') ? trim($request->string('description')->value()) : null,
            'frequency' => $request->enum('frequency', Frequency::class),
            'start_date' => $request->string('start_date')->value(),
            'end_date' => $endDate,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // If end_date passed, mark ended
        if ($recurringTransaction->end_date && $recurringTransaction->next_run_date && $recurringTransaction->next_run_date->greaterThan($recurringTransaction->end_date)) {
            $recurringTransaction->update(['next_run_date' => null]);
        }

        return redirect()->route('recurring.index')->with('success', 'Recurring rule updated.');
    }

    /**
     * Remove the recurring rule from storage (existing generated transactions remain).
     */
    public function destroy(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $this->authorize('delete', $recurringTransaction);

        $recurringTransaction->delete();

        return redirect()->route('recurring.index')->with('success', 'Recurring rule deleted.');
    }

    /**
     * Pause or resume a recurring rule.
     */
    public function toggle(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $this->authorize('toggle', $recurringTransaction);

        if ($recurringTransaction->is_active) {
            $recurringTransaction->update(['is_active' => false]);

            return redirect()->route('recurring.index')->with('success', 'Rule paused.');
        }

        // Resume: compute first occurrence on or after today
        $resumeData = RecurrenceCalculator::nextOccurrenceOnOrAfter(
            $recurringTransaction->start_date,
            $recurringTransaction->frequency,
            $recurringTransaction->runs_count
        );

        $nextRun = $resumeData['next_run_date'];
        if ($recurringTransaction->end_date && $nextRun->greaterThan($recurringTransaction->end_date)) {
            $nextRun = null;
        }

        $recurringTransaction->update([
            'is_active' => true,
            'runs_count' => $resumeData['runs_count'],
            'next_run_date' => $nextRun,
        ]);

        return redirect()->route('recurring.index')->with('success', 'Rule resumed.');
    }

    /**
     * Process due recurring transactions immediately for the signed-in user.
     */
    public function processDue(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $count = $this->processor->processForUser($user);

        if ($count > 0) {
            $msg = sprintf('Created %d %s.', $count, Str::plural('transaction', $count));

            return redirect()->route('recurring.index')->with('success', $msg);
        }

        return redirect()->route('recurring.index')->with('info', 'Nothing is due.');
    }
}
