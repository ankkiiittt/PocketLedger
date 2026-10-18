<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MonthRange;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        public ReportService $reportService
    ) {}

    /**
     * Display reports page for a selected month.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $monthParam = $request->query('month');
        $monthRange = MonthRange::fromString(is_string($monthParam) ? $monthParam : null);

        $totals = $this->reportService->getMonthTotals($user, $monthRange);
        $breakdown = $this->reportService->getCategoryBreakdown($user, $monthRange);
        $monthlyTrend = $this->reportService->getMonthlyTrend($user, $monthRange);
        $topExpenses = $this->reportService->getTopExpenses($user, $monthRange, 5);

        $hasDataInMonth = (bccomp($totals['income'], '0', 2) > 0 || bccomp($totals['expense'], '0', 2) > 0);

        $defaultFrom = $monthRange->startDateString();
        $defaultTo = $monthRange->start->copy()->endOfMonth()->toDateString();

        return view('reports.index', [
            'monthRange' => $monthRange,
            'totals' => $totals,
            'breakdown' => $breakdown,
            'monthlyTrend' => $monthlyTrend,
            'topExpenses' => $topExpenses,
            'hasDataInMonth' => $hasDataInMonth,
            'defaultFrom' => $defaultFrom,
            'defaultTo' => $defaultTo,
            'userCurrency' => $user->currency_code,
        ]);
    }

    /**
     * Stream CSV export of transactions for date range.
     */
    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $fromDate = Carbon::createFromFormat('Y-m-d', $request->string('from')->value());
        $toDate = Carbon::createFromFormat('Y-m-d', $request->string('to')->value());

        if ($fromDate->diffInDays($toDate) > 1826) {
            return back()->withErrors(['to' => 'The export date range cannot exceed 5 years.']);
        }

        /** @var User $user */
        $user = $request->user();

        return $this->reportService->exportTransactionsCsv(
            $user,
            $fromDate->toDateString(),
            $toDate->toDateString()
        );
    }
}
