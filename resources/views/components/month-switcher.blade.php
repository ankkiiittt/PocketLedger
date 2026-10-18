@props([
    'monthRange' => null,
    'currentMonth' => null,
    'currentLabel' => null,
    'prevMonth' => null,
    'nextMonth' => null,
    'baseUrl' => request()->url(),
])

@php
    if ($monthRange instanceof \App\Services\MonthRange) {
        $currentMonth = $monthRange->currentMonth;
        $currentLabel = $monthRange->label;
        $prevMonth = $monthRange->prevMonth;
        $nextMonth = $monthRange->nextMonth;
        $isCurrentMonth = $monthRange->isCurrentMonth;
    } else {
        $thisMonth = now()->format('Y-m');
        $isCurrentMonth = ($currentMonth === $thisMonth);
    }

    $queryParams = request()->except('month', 'page');
    $prevUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['month' => $prevMonth]));
    $nextUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['month' => $nextMonth]));
    $thisMonthUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['month' => now()->format('Y-m')]));
@endphp

<div class="month-switcher" role="navigation" aria-label="Month switcher">
    <a href="{{ $prevUrl }}" class="month-nav-btn" aria-label="Previous month ({{ $prevMonth }})">
        <x-icon name="chevron-left" size="18" />
    </a>
    <span class="month-label" aria-live="polite">{{ $currentLabel }}</span>
    <a href="{{ $nextUrl }}" class="month-nav-btn" aria-label="Next month ({{ $nextMonth }})">
        <x-icon name="chevron-right" size="18" />
    </a>
    @if (!$isCurrentMonth)
        <a href="{{ $thisMonthUrl }}" class="this-month-link">
            This month
        </a>
    @endif
</div>
