<x-guest-layout title="Set New Password">
    <h1 class="text-h2" style="margin-bottom: var(--space-4); text-align: center;">Set new password</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-field
            name="email"
            label="Email address"
            type="email"
            :value="old('email', $email)"
            required
            autocomplete="email"
            readonly
        />

        <x-field
            name="password"
            label="New password (min 8 characters)"
            type="password"
            required
            autocomplete="new-password"
            autofocus
        />

        <x-field
            name="password_confirmation"
            label="Confirm new password"
            type="password"
            required
            autocomplete="new-password"
        />

        <div style="margin-top: var(--space-5);">
            <x-button variant="primary" type="submit" block loadingText="Updating password...">
                Reset password
            </x-button>
        </div>

        <p class="text-small" style="text-align: center; margin-top: var(--space-4); color: var(--color-text-muted);">
            <a href="{{ route('login') }}" style="font-weight: 600;">Back to sign in</a>
        </p>
    </form>
</x-guest-layout>
