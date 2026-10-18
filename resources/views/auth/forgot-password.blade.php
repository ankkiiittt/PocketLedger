<x-guest-layout title="Recover Password">
    <h1 class="text-h2" style="margin-bottom: var(--space-2); text-align: center;">Reset your password</h1>
    <p class="text-body" style="color: var(--color-text-muted); font-size: 0.875rem; text-align: center; margin-bottom: var(--space-5);">
        Enter your account email and we'll send you a link to reset your password.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <x-field
            name="email"
            label="Email address"
            type="email"
            :value="old('email')"
            required
            autocomplete="email"
            autofocus
        />

        <div style="margin-top: var(--space-5);">
            <x-button variant="primary" type="submit" block loadingText="Sending link...">
                Send reset link
            </x-button>
        </div>

        <p class="text-small" style="text-align: center; margin-top: var(--space-4); color: var(--color-text-muted);">
            <a href="{{ route('login') }}" style="font-weight: 600;">Back to sign in</a>
        </p>
    </form>
</x-guest-layout>
