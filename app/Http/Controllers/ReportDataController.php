<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MonthRange;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportDataController extends Controller
{
    public function __construct(
        public ReportService $reportService
    ) {}

    /**
     * Return JSON for category breakdown (Appendix C).
     */
    public function categoryBreakdown(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $monthParam = $request->query('month');
        $monthRange = MonthRange::fromString(is_string($monthParam) ? $monthParam : null);

        $breakdown = $this->reportService->getCategoryBreakdown($user, $monthRange);

        $items = array_map(function ($item) {
            return [
                'category_id' => $item['category_id'],
                'name' => $item['name'],
                'color' => $item['color'],
                'amount' => $item['amount'],
                'percent' => $item['percent'],
            ];
        }, $breakdown['items']);

        return response()->json([
            'month' => $monthRange->currentMonth,
            'currency' => $user->currency_code,
            'total' => $breakdown['total'],
            'items' => $items,
        ]);
    }

    /**
     * Return JSON for 6-month trend (Appendix C).
     */
    public function monthlyTrend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $monthParam = $request->query('month');
        $monthRange = MonthRange::fromString(is_string($monthParam) ? $monthParam : null);

        $trend = $this->reportService->getMonthlyTrend($user, $monthRange);

        return response()->json([
            'currency' => $user->currency_code,
            'months' => $trend,
        ]);
    }
}
