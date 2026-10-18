<x-app-layout title="Account Settings" header="Account Settings">
    <div style="max-width: 600px; display: flex; flex-direction: column; gap: var(--space-7);">

        <!-- Section 1: Profile Information -->
        <section class="settings-section" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-6);">
            <div style="margin-bottom: var(--space-5);">
                <h2 class="text-h2">Profile & Currency</h2>
                <p class="text-body" style="color: var(--color-text-muted); font-size: 0.875rem; margin-top: 2px;">
                    Update your account identity and display currency.
                </p>
            </div>

            <form method="POST" action="{{ route('settings.profile') }}">
                @csrf
                @method('PUT')

                <x-field
                    name="name"
                    label="Full name"
                    :value="old('name', $user->name)"
                    required
                />

                <x-field
                    name="email"
                    label="Email address"
                    type="email"
                    :value="old('email', $user->email)"
                    required
                />

                <x-field
                    name="currency_code"
                    label="Display currency"
                    type="select"
                    required
                    help="Note: Changing currency changes the display symbol only; stored transaction amounts are not converted."
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
                        $userCurrency = old('currency_code', $user->currency_code);
                    @endphp
                    @foreach ($currencies as $code => $label)
                        <option value="{{ $code }}" {{ $userCurrency === $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </x-field>

                <div style="margin-top: var(--space-5); display: flex; justify-content: flex-end;">
                    <x-button variant="primary" type="submit" loadingText="Saving...">
                        Save profile
                    </x-button>
                </div>
            </form>
        </section>

        <!-- Section 2: Change Password -->
        <section class="settings-section" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-6);">
            <div style="margin-bottom: var(--space-5);">
                <h2 class="text-h2">Change Password</h2>
                <p class="text-body" style="color: var(--color-text-muted); font-size: 0.875rem; margin-top: 2px;">
                    Ensure your account is using a secure password.
                </p>
            </div>

            <form method="POST" action="{{ route('settings.password') }}">
                @csrf
                @method('PUT')

                <x-field
                    name="current_password"
                    label="Current password"
                    type="password"
                    required
                    autocomplete="current-password"
                />

                <x-field
                    name="password"
                    label="New password (min 8 characters)"
                    type="password"
                    required
                    autocomplete="new-password"
                />

                <x-field
                    name="password_confirmation"
                    label="Confirm new password"
                    type="password"
                    required
                    autocomplete="new-password"
                />

                <div style="margin-top: var(--space-5); display: flex; justify-content: flex-end;">
                    <x-button variant="primary" type="submit" loadingText="Updating password...">
                        Change password
                    </x-button>
                </div>
            </form>
        </section>

        <!-- Section 3: Danger Zone (Delete Account) -->
        <section class="settings-section" style="background: var(--color-surface); border: 1px solid var(--color-border); border-top: 3px solid var(--color-danger); border-radius: var(--radius-md); padding: var(--space-6);">
            <div style="margin-bottom: var(--space-4);">
                <h2 class="text-h2" style="color: var(--color-danger);">Danger Zone</h2>
                <p class="text-body" style="color: var(--color-text-muted); font-size: 0.875rem; margin-top: 2px;">
                    Permanently delete your account and all associated financial records. This action cannot be undone.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('settings.destroy') }}"
                data-confirm="Are you sure you want to delete your account? All your categories, transactions, budgets, and recurring rules will be permanently removed."
            >
                @csrf
                @method('DELETE')

                <x-field
                    name="password"
                    label="Confirm your password to delete"
                    type="password"
                    required
                    autocomplete="current-password"
                />

                <div style="margin-top: var(--space-5); display: flex; justify-content: flex-end;">
                    <x-button variant="danger" type="submit" loadingText="Deleting account...">
                        Delete account and all data
                    </x-button>
                </div>
            </form>
        </section>

    </div>
</x-app-layout>
