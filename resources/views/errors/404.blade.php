<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found - PocketLedger</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>
<body>
    <div class="guest-shell">
        <div class="auth-container" style="text-align: center;">
            <div class="auth-brand">
                <span class="text-h1">PocketLedger</span>
            </div>
            <div class="auth-card" style="text-align: center;">
                <h1 class="text-h2" style="margin-bottom: var(--space-3);">Page not found</h1>
                <p class="text-body" style="color: var(--color-text-muted); margin-bottom: var(--space-6);">
                    The page or record you requested does not exist or has been moved.
                </p>
                <x-button variant="primary" :href="auth()->check() ? route('dashboard') : url('/login')">
                    {{ auth()->check() ? 'Return to Dashboard' : 'Go to Sign In' }}
                </x-button>
            </div>
        </div>
    </div>
</body>
</html>
