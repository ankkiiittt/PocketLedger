<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetStatusService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        public BudgetStatusService $budgetStatusService
    ) {}

    /**
     * Display a listing of transactions with filters, search, and totals.
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $hasAnyTransactions = $user->transactions()->exists();

        // Defaults for date range: current month if no filter params are present
        $hasFilterParams = $request->hasAny(['from', 'to', 'type', 'category_id', 'q', 'page']);
        $defaultFrom = Carbon::now()->startOfMonth()->toDateString();
        $defaultTo = Carbon::now()->endOfMonth()->toDateString();

        $from = $request->has('from') ? $request->query('from') : ($hasFilterParams ? null : $defaultFrom);
        $to = $request->has('to') ? $request->query('to') : ($hasFilterParams ? null : $defaultTo);

        if ($from !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $from)) {
            $from = null;
        }
        if ($to !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $to)) {
            $to = null;
        }

        $type = $request->query('type');
        if (! in_array($type, ['income', 'expense'], true)) {
            $type = null;
        }

        $categoryId = $request->filled('category_id') ? (int) $request->query('category_id') : null;
        if ($categoryId !== null) {
            $ownsCategory = $user->categories()->where('id', $categoryId)->exists();
            if (! $ownsCategory) {
                $categoryId = null;
            }
        }

        $search = $request->filled('q') ? (string) $request->query('q') : null;
        if ($search !== null && mb_strlen($search) > 100) {
            $search = mb_substr($search, 0, 100);
        }

        // Query transactions
        $query = $user->transactions()
            ->with(['category', 'recurringTransaction']);

        if ($type !== null) {
            $query->whereHas('category', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($from !== null) {
            $query->where('transaction_date', '>=', $from);
        }

        if ($to !== null) {
            $query->where('transaction_date', '<=', $to);
        }

        if ($search !== null && $search !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where('description', 'LIKE', '%'.$escaped.'%');
        }

        $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        $transactions = $query->paginate(20)->withQueryString();

        if ($transactions->currentPage() > $transactions->lastPage() && $transactions->lastPage() > 0) {
            return redirect($request->fullUrlWithQuery(['page' => $transactions->lastPage()]));
        }

        // Query totals across the entire filtered set
        $totalsQuery = DB::table('transactions')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $user->id);

        if ($type !== null) {
            $totalsQuery->where('categories.type', $type);
        }

        if ($categoryId !== null) {
            $totalsQuery->where('transactions.category_id', $categoryId);
        }

        if ($from !== null) {
            $totalsQuery->where('transactions.transaction_date', '>=', $from);
        }

        if ($to !== null) {
            $totalsQuery->where('transactions.transaction_date', '<=', $to);
        }

        if ($search !== null && $search !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $totalsQuery->where('transactions.description', 'LIKE', '%'.$escaped.'%');
        }

        $totalsGroup = $totalsQuery->select('categories.type', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.type')
            ->pluck('total', 'type');

        $incomeTotal = (string) ($totalsGroup['income'] ?? '0.00');
        $expenseTotal = (string) ($totalsGroup['expense'] ?? '0.00');
        $netTotal = bcsub($incomeTotal, $expenseTotal, 2);

        $filterCategories = $user->categories()->orderBy('name')->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'netTotal' => $netTotal,
            'userCurrency' => $user->currency_code,
            'hasAnyTransactions' => $hasAnyTransactions,
            'filterCategories' => $filterCategories,
            'currentFrom' => $from,
            'currentTo' => $to,
            'currentType' => $type,
            'currentCategoryId' => $categoryId,
            'currentQuery' => $search,
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $categories = $user->categories()
            ->active()
            ->orderBy('name')
            ->get();

        return view('transactions.create', [
            'categories' => $categories,
            'userCurrency' => $user->currency_code,
            'today' => Carbon::now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Transaction $transaction */
        $transaction = $user->transactions()->create([
            'category_id' => $request->integer('category_id'),
            'amount' => $request->string('amount')->value(),
            'transaction_date' => $request->string('transaction_date')->value(),
            'description' => $request->filled('description') ? trim($request->string('description')->value()) : null,
        ]);

        /** @var Category $category */
        $category = Category::findOrFail($transaction->category_id);
        $flashMessage = 'Transaction saved.';

        if ($category->type === CategoryType::Expense) {
            $budgetMsg = $this->budgetStatusService->getSavedExpenseMessage(
                $user,
                $category,
                $transaction->transaction_date->toDateString()
            );

            if ($budgetMsg !== null) {
                $flashMessage = $budgetMsg;
            }
        }

        if ($request->input('action') === 'add_another') {
            return redirect()->route('transactions.create')->with('success', $flashMessage);
        }

        return redirect()->route('transactions.index')->with('success', $flashMessage);
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        /** @var User $user */
        $user = $request->user();

        // Include active categories and the transaction's category if archived
        $categories = $user->categories()
            ->where(function ($q) use ($transaction) {
                $q->active()->orWhere('id', $transaction->category_id);
            })
            ->orderBy('name')
            ->get();

        $returnTo = $request->input('return_to');
        if (! is_string($returnTo) || ! str_starts_with($returnTo, '/transactions') || str_starts_with($returnTo, '//')) {
            $returnTo = null;
        }

        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => $categories,
            'userCurrency' => $user->currency_code,
            'returnTo' => $returnTo,
        ]);
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update([
            'category_id' => $request->integer('category_id'),
            'amount' => $request->string('amount')->value(),
            'transaction_date' => $request->string('transaction_date')->value(),
            'description' => $request->filled('description') ? trim($request->string('description')->value()) : null,
        ]);

        $returnTo = $request->input('return_to');
        $destination = (is_string($returnTo) && str_starts_with($returnTo, '/transactions') && ! str_starts_with($returnTo, '//'))
            ? $returnTo
            : route('transactions.index');

        return redirect($destination)->with('success', 'Transaction updated.');
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted.');
    }
}
