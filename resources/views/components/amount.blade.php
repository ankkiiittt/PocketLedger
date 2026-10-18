@props([
    'value',       // Formatted money string (e.g. ₹12,000.00 or 12,000.00)
    'type' => 'neutral', // income, expense, neutral
    'signed' => true,
    'class' => '',
])

@php
    $typeClass = match ($type) {
        'income' => 'amount-income',
        'expense' => 'amount-expense',
        default => 'amount-neutral',
    };
@endphp

<span class="table-amount {{ $typeClass }} {{ $class }}">
    @if ($type === 'income')
        <span class="sr-only">Income</span>@if($signed)+@endif
    @elseif ($type === 'expense')
        <span class="sr-only">Expense</span>@if($signed)-@endif
    @endif
    {{ $value }}
</span>
