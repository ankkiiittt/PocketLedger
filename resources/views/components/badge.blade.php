@props([
    'variant' => 'neutral',
    'text' => null,
])

@php
$classes = 'badge';
if ($variant && $variant !== 'neutral') {
    $classes .= ' badge-' . $variant;
} elseif (is_string($text)) {
    $lower = strtolower($text);
    if (in_array($lower, ['active', 'paused', 'ended', 'archived'], true)) {
        $classes .= ' badge-' . $lower;
    }
}
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $text ?? $slot }}
</span>
