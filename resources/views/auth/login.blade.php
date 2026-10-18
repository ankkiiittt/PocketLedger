<x-guest-layout title="Sign In">
    <h1 class="text-h2" style="margin-bottom: var(--space-4); text-align: center;">Sign in to your ledger</h1>

    <form method="POST" action="{{ route('login.store') }}">
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

        <x-field
            name="password"
            label="Password"
            type="password"
            required
            autocomplete="current-password"
        />

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <x-field
                name="remember"
                label="Remember me"
                type="checkbox"
                value="1"
            />

            <a href="{{ route('password.request') }}" class="text-small" style="font-weight: 500;">
                Forgot password?
            </a>
        </div>

        <div>
            <x-button variant="primary" type="submit" block loadingText="Signing in...">
                Sign in
            </x-button>
        </div>

        <p class="text-small" style="text-align: center; margin-top: var(--space-4); color: var(--color-text-muted);">
            Don't have an account?
            <a href="{{ route('register') }}" style="font-weight: 600;">Create an account</a>
        </p>
    </form>
</x-guest-layout>
