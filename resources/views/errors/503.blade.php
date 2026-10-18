<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Unavailable - PocketLedger</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>
<body>
    <div class="guest-shell">
        <div class="auth-container" style="text-align: center;">
            <div class="auth-brand">
                <span class="text-h1">PocketLedger</span>
            </div>
            <div class="auth-card" style="text-align: center;">
                <h1 class="text-h2" style="margin-bottom: var(--space-3);">Maintenance in progress</h1>
                <p class="text-body" style="color: var(--color-text-muted); margin-bottom: var(--space-6);">
                    PocketLedger is undergoing scheduled maintenance. Please check back shortly.
                </p>
                <x-button variant="primary" :href="url('/')">
                    Reload page
                </x-button>
            </div>
        </div>
    </div>
</body>
</html>
