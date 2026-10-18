@props([
    'variant' => 'primary', // primary, secondary, ghost, danger
    'size' => 'md',        // md, sm
    'type' => 'button',
    'href' => null,
    'block' => false,
    'loadingText' => null,
])

@php
    $classes = 'btn btn-' . $variant;
    if ($size === 'sm') {
        $classes .= ' btn-sm';
    }
    if ($block) {
        $classes .= ' btn-block';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if ($loadingText) data-loading-text="{{ $loadingText }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
