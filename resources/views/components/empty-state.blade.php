@props([
    'title',
    'message',
    'actionText' => null,
    'actionUrl' => null,
])

<div class="empty-state">
    <div class="empty-state-motif" aria-hidden="true">
        <div class="ledger-line"></div>
        <div class="ledger-line medium"></div>
        <div class="ledger-line short"></div>
    </div>
    <h3 class="empty-state-title">{{ $title }}</h3>
    <p class="empty-state-desc">{{ $message }}</p>
    @if ($actionText && $actionUrl)
        <x-button variant="primary" :href="$actionUrl">
            {{ $actionText }}
        </x-button>
    @endif
</div>
