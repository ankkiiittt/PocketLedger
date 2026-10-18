<x-guest-layout title="Create an Account">
    <h1 class="text-h2" style="margin-bottom: var(--space-4); text-align: center;">Create an account</h1>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <x-field
            name="name"
            label="Full name"
            :value="old('name')"
            required
            autocomplete="name"
            autofocus
        />

        <x-field
            name="email"
            label="Email address"
            type="email"
            :value="old('email')"
            required
            autocomplete="email"
        />

        <x-field
            name="password"
            label="Password (min 8 characters)"
            type="password"
            required
            autocomplete="new-password"
        />

        <x-field
            name="password_confirmation"
            label="Confirm password"
            type="password"
            required
            autocomplete="new-password"
        />

        <x-field
            name="currency_code"
            label="Currency"
            type="select"
            required
            help="Choose your primary currency. PocketLedger tracks your finances in this currency."
        >
            @php
                $currencies = [
                    'INR' => 'INR - Indian Rupee (₹)',
                    'USD' => 'USD - US Dollar ($)',
                    'EUR' => 'EUR - Euro (€)',
                    'GBP' => 'GBP - British Pound (£)',
                    'AED' => 'AED - UAE Dirham',
                    'SGD' => 'SGD - Singapore Dollar (S$)',
                    'AUD' => 'AUD - Australian Dollar (A$)',
                    'CAD' => 'CAD - Canadian Dollar (C$)',
                ];
                $selectedCurrency = old('currency_code', 'INR');
            @endphp
            @foreach ($currencies as $code => $label)
                <option value="{{ $code }}" {{ $selectedCurrency === $code ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </x-field>

        <div style="margin-top: var(--space-5);">
            <x-button variant="primary" type="submit" block loadingText="Creating account...">
                Create account
            </x-button>
        </div>

        <p class="text-small" style="text-align: center; margin-top: var(--space-4); color: var(--color-text-muted);">
            Already have an account?
            <a href="{{ route('login') }}" style="font-weight: 600;">Sign in</a>
        </p>
    </form>
</x-guest-layout>
