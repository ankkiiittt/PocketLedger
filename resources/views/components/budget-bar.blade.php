@props([
    'percent' => 0,
    'status' => 'on_track', // on_track, nearing_limit, over_budget
    'statusText' => null,
    'spent' => null,
    'limit' => null,
])

@php
    $safePercent = (float) $percent;
    $barWidth = min(max($safePercent, 0), 100);

    $statusClass = match ($status) {
        'nearing_limit' => 'status-nearing-limit',
        'over_budget' => 'status-over-budget',
        default => 'status-on-track',
    };

    $fillClass = match ($status) {
        'nearing_limit' => 'fill-nearing-limit',
        'over_budget' => 'fill-over-budget',
        default => 'fill-on-track',
    };

    $defaultStatusText = match ($status) {
        'nearing_limit' => 'Nearing limit',
        'over_budget' => 'Over budget',
        default => 'On track',
    };

    $displayStatus = $statusText ?? $defaultStatusText;
@endphp

<div class="budget-bar-wrap {{ $statusClass }}">
    <div class="budget-bar-header">
        <span class="budget-status-text">{{ $displayStatus }} ({{ round($safePercent) }}%)</span>
        @if ($spent !== null && $limit !== null)
            <span class="text-caption text-muted tabular-nums">{{ $spent }} / {{ $limit }}</span>
        @endif
    </div>
    <div class="budget-bar-track" role="progressbar" aria-valuenow="{{ round($safePercent) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Budget used">
        <div class="budget-bar-fill {{ $fillClass }}" style="width: {{ $barWidth }}%;"></div>
    </div>
</div>
