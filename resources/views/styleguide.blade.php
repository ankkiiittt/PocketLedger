<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Design System & Styleguide - PocketLedger</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>
<body style="background-color: var(--color-bg); padding: var(--space-6) var(--space-4);">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <main id="main-content" style="max-width: var(--content-max-width); margin: 0 auto;">
        <div style="margin-bottom: var(--space-7); border-bottom: 2px solid var(--color-border); padding-bottom: var(--space-4);">
            <h1 class="text-h1">PocketLedger Design System</h1>
            <p class="text-body" style="color: var(--color-text-muted); margin-top: var(--space-2);">
                Living styleguide displaying design tokens, typography, components, and interaction states.
            </p>
        </div>

        <!-- 1. Color Palette Tokens -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">1. Color Tokens & 8 Category Presets</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: var(--space-3);">
                <div style="background: var(--color-bg); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-bg); border: 1px solid var(--color-border); margin-bottom: 4px;"></div>
                    <span class="text-caption">--color-bg</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-primary); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Primary (#1F5F4D)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-income); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Income (#2E7D4F)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-expense); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Expense (#B4432F)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-warning); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Warning (#B7791F)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-pine); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Pine (#1F5F4D)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-brick); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Brick (#B4432F)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-slate); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Slate (#2F5F8A)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-ochre); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Ochre (#B7791F)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-plum); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Plum (#6B4E8F)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-teal); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Teal (#3D8B8B)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-umber); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Umber (#8A5A44)</span>
                </div>
                <div style="background: var(--color-surface); padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                    <div style="height: 32px; background: var(--color-cat-olive); border-radius: 4px; margin-bottom: 4px;"></div>
                    <span class="text-caption">Olive (#6F7D3A)</span>
                </div>
            </div>
        </section>

        <!-- 2. Typography Scale -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">2. Typography</h2>
            <div style="background: var(--color-surface); padding: var(--space-5); border: 1px solid var(--color-border); border-radius: var(--radius-md); display: flex; flex-direction: column; gap: var(--space-3);">
                <h1 class="text-h1">Page Title H1 (Fraunces 28px/36px)</h1>
                <h2 class="text-h2">Section Title H2 (Fraunces 22px)</h2>
                <h3 class="text-h3">Subsection Title H3 (Inter 16px weight 600)</h3>
                <p class="text-body">Body text: Orderly, trustworthy, and quiet personal financial ledger.</p>
                <span class="text-small">Small & Labels (Inter 14px weight 500)</span>
                <span class="text-caption">Caption & Headers (Inter 12px uppercase 0.04em letter spacing)</span>
                <span class="text-figure tabular-nums">Large figures: ₹1,24,500.00 (Fraunces 28px tabular-nums)</span>
            </div>
        </section>

        <!-- 3. Summary Tiles (x-stat) -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">3. Summary Tiles (x-stat)</h2>
            <div class="summary-tiles-grid">
                <x-stat label="Income" value="₹75,000.00" rule="income" subtitle="October 2026" />
                <x-stat label="Expenses" value="₹42,350.00" rule="expense" subtitle="October 2026" />
                <x-stat label="Net" value="₹32,650.00" rule="neutral" subtitle="Savings rate 43.5%" />
                <x-stat label="Budget used" value="68%" rule="budget" subtitle="₹42,350 of ₹62,000" />
            </div>
        </section>

        <!-- 4. Buttons (x-button) -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">4. Buttons & Interaction States</h2>
            <div style="display: flex; flex-wrap: wrap; gap: var(--space-3); align-items: center; background: var(--color-surface); padding: var(--space-5); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <x-button variant="primary">Primary Button</x-button>
                <x-button variant="secondary">Secondary Button</x-button>
                <x-button variant="ghost">Ghost Button</x-button>
                <x-button variant="danger">Danger Button</x-button>
                <x-button variant="primary" size="sm">Small Button</x-button>
                <x-button variant="primary" disabled>Disabled</x-button>
            </div>
        </section>

        <!-- 5. Form Fields (x-field) -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">5. Form Fields (x-field)</h2>
            <div style="background: var(--color-surface); padding: var(--space-5); border: 1px solid var(--color-border); border-radius: var(--radius-md); max-width: 480px;">
                <form id="styleguide-sample-form" method="POST" action="#" data-no-guard>
                    <x-field name="sample_name" label="Description" placeholder="e.g. Grocery shopping" required />
                    <x-field name="sample_amount" label="Amount" inputmode="decimal" placeholder="0.00" suffix="INR" required />
                    <x-field name="sample_category" label="Category" type="select" required>
                        <option value="">Select a category</option>
                        <option value="1">Groceries</option>
                        <option value="2">Rent</option>
                    </x-field>
                    <x-field name="sample_notes" label="Notes" type="textarea" placeholder="Optional details..." />
                    <x-field name="sample_error" label="Field with error" value="Invalid entry" error="The amount must be greater than zero." required />
                    <x-field name="sample_checkbox" label="I accept recurring auto-deduction" type="checkbox" />
                </form>
            </div>
        </section>

        <!-- 6. Flash Messages (x-flash) -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">6. Flash Banners (x-flash)</h2>
            <x-flash type="success" message="Saved. Groceries: ₹12,000.00 of ₹15,000.00 used in Oct 2026 (80%)." />
            <x-flash type="warning" message="Warning: Dining Out is nearing its monthly limit." />
            <x-flash type="error" message="These credentials do not match our records." />
            <x-flash type="info" message="Welcome to PocketLedger. Your default categories are ready." />
        </section>

        <!-- 7. Budget Progress Bar (x-budget-bar) -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">7. Budget Progress Bars (x-budget-bar)</h2>
            <div style="background: var(--color-surface); padding: var(--space-5); border: 1px solid var(--color-border); border-radius: var(--radius-md); display: flex; flex-direction: column; gap: var(--space-4);">
                <div>
                    <span class="text-small" style="font-weight: 600; margin-bottom: 4px; display: block;">Groceries</span>
                    <x-budget-bar percent="65" status="on_track" statusText="On track" spent="₹6,500.00" limit="₹10,000.00" />
                </div>
                <div>
                    <span class="text-small" style="font-weight: 600; margin-bottom: 4px; display: block;">Dining Out</span>
                    <x-budget-bar percent="88" status="nearing_limit" statusText="Nearing limit" spent="₹4,400.00" limit="₹5,000.00" />
                </div>
                <div>
                    <span class="text-small" style="font-weight: 600; margin-bottom: 4px; display: block;">Entertainment</span>
                    <x-budget-bar percent="120" status="over_budget" statusText="Over budget" spent="₹3,600.00" limit="₹3,000.00" />
                </div>
            </div>
        </section>

        <!-- 8. Ledger Ruled Table & Amounts -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">8. Ledger-Style Ruled Table (x-amount)</h2>
            <div class="totals-strip">
                <div class="totals-item">
                    <span class="totals-label">Income:</span>
                    <span class="totals-value" style="color: var(--color-income);">₹75,000.00</span>
                </div>
                <div class="totals-item">
                    <span class="totals-label">Expenses:</span>
                    <span class="totals-value" style="color: var(--color-expense);">₹18,250.00</span>
                </div>
                <div class="totals-item">
                    <span class="totals-label">Net:</span>
                    <span class="totals-value">₹56,750.00</span>
                </div>
            </div>

            <div class="ledger-table-wrap" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <table class="ledger-table responsive-stack-table">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Description</th>
                            <th scope="col">Category</th>
                            <th scope="col" class="th-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tabular-nums">18 Oct 2026</td>
                            <td>Monthly Salary <span class="badge badge-active">Recurring</span></td>
                            <td><span class="category-badge-item"><span class="color-dot" style="background: var(--color-cat-pine);"></span> Salary</span></td>
                            <td class="td-right"><x-amount value="₹65,000.00" type="income" /></td>
                        </tr>
                        <tr>
                            <td class="tabular-nums">17 Oct 2026</td>
                            <td>Supermarket groceries</td>
                            <td><span class="category-badge-item"><span class="color-dot" style="background: var(--color-cat-teal);"></span> Groceries</span></td>
                            <td class="td-right"><x-amount value="₹3,450.00" type="expense" /></td>
                        </tr>
                        <tr>
                            <td class="tabular-nums">15 Oct 2026</td>
                            <td>Coffee & sandwiches</td>
                            <td><span class="category-badge-item"><span class="color-dot" style="background: var(--color-cat-brick);"></span> Dining Out</span></td>
                            <td class="td-right"><x-amount value="₹480.00" type="expense" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 9. Month Switcher (x-month-switcher) -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">9. Month Switcher (x-month-switcher)</h2>
            <x-month-switcher currentMonth="2026-10" currentLabel="October 2026" prevMonth="2026-09" nextMonth="2026-11" baseUrl="/_styleguide" />
        </section>

        <!-- 10. Empty State (x-empty-state) -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">10. Empty State (x-empty-state)</h2>
            <x-empty-state
                title="No transactions yet"
                message="Start logging your income and expenses to track your monthly budget."
                actionText="Add your first transaction"
                actionUrl="#add-transaction"
            />
        </section>

        <!-- 11. Confirmation Dialog Trigger -->
        <section class="page-section">
            <h2 class="text-h2" style="margin-bottom: var(--space-4);">11. Confirmation Dialog Interceptor</h2>
            <div style="background: var(--color-surface); padding: var(--space-5); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <p class="text-body" style="margin-bottom: var(--space-4);">Forms with <code>data-confirm="..."</code> open a native modal dialog for safe deletion.</p>
                <form method="POST" action="#" data-confirm="Are you sure you want to delete this item? This action cannot be undone.">
                    <x-button variant="danger" type="submit">
                        Test Delete Confirmation
                    </x-button>
                </form>
            </div>
        </section>
    </main>

    <!-- Modal dialog for confirmation -->
    <dialog id="confirm-modal" class="confirm-modal" aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message">
        <div class="modal-header">
            <h2 id="confirm-modal-title" class="modal-title">Please confirm</h2>
        </div>
        <p id="confirm-modal-message" class="modal-body">
            Are you sure you want to proceed?
        </p>
        <div class="modal-actions">
            <x-button variant="secondary" id="confirm-modal-cancel">
                Cancel
            </x-button>
            <x-button variant="danger" id="confirm-modal-confirm">
                Confirm
            </x-button>
        </div>
    </dialog>
</body>
</html>
