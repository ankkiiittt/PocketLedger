@props([
    'type' => 'success', // success, error, warning, info
    'message' => null,
])

@php
    $role = in_array($type, ['error', 'warning']) ? 'alert' : 'status';
    $iconName = match ($type) {
        'success' => 'check',
        'error' => 'alert-circle',
        'warning' => 'alert-triangle',
        'info' => 'info',
        default => 'info',
    };
@endphp

<div class="flash-banner flash-{{ $type }}" role="{{ $role }}">
    <div class="flash-content">
        <span class="flash-icon">
            <x-icon :name="$iconName" size="18" />
        </span>
        <span class="flash-text">
            {{ $message ?? $slot }}
        </span>
    </div>
    <button type="button" class="flash-dismiss" data-flash-dismiss aria-label="Dismiss notice">
        <x-icon name="close" size="16" />
    </button>
</div>
