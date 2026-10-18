@props([
    'title',
    'message' => null,
    'description' => null,
    'actionText' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

@php
$desc = $message ?? $description ?? $slot;
$label = $actionLabel ?? $actionText;
@endphp

<div class="empty-state">
    <div class="empty-state-motif" aria-hidden="true">
        <div class="ledger-line"></div>
        <div class="ledger-line medium"></div>
        <div class="ledger-line short"></div>
    </div>
    <h3 class="empty-state-title">{{ $title }}</h3>
    @if ($desc)
        <p class="empty-state-desc">{{ $desc }}</p>
    @endif
    @if ($label && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary">
            {{ $label }}
        </a>
    @endif
</div>
