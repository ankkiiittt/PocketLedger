<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? "{$title} - PocketLedger" : "PocketLedger" }}</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>
<body>
    <div class="guest-shell">
        <div class="auth-container">
            <div class="auth-brand">
                <a href="{{ url('/') }}" class="text-h1" style="color: var(--color-text); text-decoration: none;">
                    PocketLedger
                </a>
                <p class="text-small" style="color: var(--color-text-muted); margin-top: var(--space-1);">
                    Personal expense and budget tracker
                </p>
            </div>

            @if (session('success'))
                <x-flash type="success" :message="session('success')" />
            @endif
            @if (session('error'))
                <x-flash type="error" :message="session('error')" />
            @endif
            @if (session('warning'))
                <x-flash type="warning" :message="session('warning')" />
            @endif
            @if (session('status'))
                <x-flash type="info" :message="session('status')" />
            @endif

            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
