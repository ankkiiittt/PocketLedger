@props([
    'currentMonth', // YYYY-MM
    'currentLabel', // e.g. October 2026
    'prevMonth',    // YYYY-MM
    'nextMonth',    // YYYY-MM
    'baseUrl' => request()->url(),
])

@php
    $thisMonth = now()->format('Y-m');
    $isCurrentMonth = ($currentMonth === $thisMonth);
    $queryParams = request()->except('month', 'page');

    $prevUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['month' => $prevMonth]));
    $nextUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['month' => $nextMonth]));
    $thisMonthUrl = $baseUrl . '?' . http_build_query(array_merge($queryParams, ['month' => $thisMonth]));
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
