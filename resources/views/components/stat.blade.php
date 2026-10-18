@props([
    'label',
    'value',
    'rule' => 'neutral', // income, expense, neutral, budget
    'subtitle' => null,
])

<div class="stat-card stat-{{ $rule }}">
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-value">{{ $value }}</div>
    @if ($subtitle)
        <div class="stat-subtitle">{{ $subtitle }}</div>
    @endif
</div>
