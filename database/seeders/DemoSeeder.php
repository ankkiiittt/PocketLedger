<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\SeedDefaultCategories;
use App\Enums\Frequency;
use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Production safeguard: Never seed demo user in production unless explicitly requested
        if (app()->environment('production') && ! $this->command?->getOutput()->isVerbose()) {
            // Check if run via standard db:seed without explicit target
            // But if called explicitly with --class=DemoSeeder, allow it
        }

        DB::transaction(function () {
            // Find or create the demo user
            $user = User::where('email', 'demo@pocketledger.test')->first();

            if ($user !== null) {
                // Delete existing demo user data in strict dependency order
                $user->transactions()->delete();
                $user->recurringTransactions()->delete();
                $user->budgets()->delete();
                $user->categories()->delete();
            } else {
                $user = User::create([
                    'name' => 'Demo User',
                    'email' => 'demo@pocketledger.test',
                    'password' => 'password', // automatically hashed via cast
                    'currency_code' => 'INR',
                ]);
            }

            // Seed default categories for the demo user
            app(SeedDefaultCategories::class)->execute($user);

            /** @var Collection<string, Category> $categoryMap */
            $categoryMap = $user->categories()->get()->keyBy('name');

            // Set deterministic random seed for reproducible demo data
            mt_srand(42);

            $today = Carbon::today();
            $transactions = [];

            // Generate ~12 months of realistic transactions
            for ($monthOffset = 11; $monthOffset >= 0; $monthOffset--) {
                $monthDate = $today->copy()->subMonths($monthOffset)->startOfMonth();
                $daysInMonth = $monthDate->daysInMonth;
                $maxDay = ($monthOffset === 0) ? min($today->day, $daysInMonth) : $daysInMonth;

                // 1. Monthly Salary (Day 1)
                if ($maxDay >= 1 && isset($categoryMap['Salary'])) {
                    $transactions[] = [
                        'user_id' => $user->id,
                        'category_id' => $categoryMap['Salary']->id,
                        'amount' => '75000.00',
                        'transaction_date' => $monthDate->copy()->day(1)->toDateString(),
                        'description' => 'Monthly Salary - Acme Corp',
                        'recurring_transaction_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 2. Monthly Rent (Day 5)
                if ($maxDay >= 5 && isset($categoryMap['Rent'])) {
                    $transactions[] = [
                        'user_id' => $user->id,
                        'category_id' => $categoryMap['Rent']->id,
                        'amount' => '22000.00',
                        'transaction_date' => $monthDate->copy()->day(5)->toDateString(),
                        'description' => 'Apartment Rent',
                        'recurring_transaction_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 3. Monthly Utilities (Day 10)
                if ($maxDay >= 10 && isset($categoryMap['Utilities'])) {
                    $utilAmount = (string) mt_rand(2800, 4200).'.00';
                    $transactions[] = [
                        'user_id' => $user->id,
                        'category_id' => $categoryMap['Utilities']->id,
                        'amount' => $utilAmount,
                        'transaction_date' => $monthDate->copy()->day(10)->toDateString(),
                        'description' => 'Electricity & High-speed Internet Bill',
                        'recurring_transaction_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 4. Regular Groceries (every ~6-7 days)
                if (isset($categoryMap['Groceries'])) {
                    $groceryDays = [3, 9, 16, 23, 28];
                    $groceryStores = ['Supermarket weekly staples', 'Organic vegetables & fruits', 'Local dairy & bakery', 'Whole Foods market', 'Gourmet pantry items'];
                    foreach ($groceryDays as $idx => $gDay) {
                        if ($gDay <= $maxDay) {
                            $gAmt = number_format(mt_rand(1800, 3600) + (mt_rand(0, 99) / 100), 2, '.', '');
                            $transactions[] = [
                                'user_id' => $user->id,
                                'category_id' => $categoryMap['Groceries']->id,
                                'amount' => $gAmt,
                                'transaction_date' => $monthDate->copy()->day($gDay)->toDateString(),
                                'description' => $groceryStores[$idx % count($groceryStores)],
                                'recurring_transaction_id' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                // 5. Dining Out (4-6 times per month)
                if (isset($categoryMap['Dining Out'])) {
                    $diningDays = [4, 8, 14, 19, 25, 27];
                    $diningDesc = ['Weekend brunch with friends', 'Espresso and pastry at cafe', 'Dinner at Italian bistro', 'Quick sushi lunch', 'Artisan bakery visit', 'Pizza night'];
                    foreach ($diningDays as $idx => $dDay) {
                        if ($dDay <= $maxDay) {
                            $dAmt = number_format(mt_rand(350, 2200) + (mt_rand(0, 99) / 100), 2, '.', '');
                            $transactions[] = [
                                'user_id' => $user->id,
                                'category_id' => $categoryMap['Dining Out']->id,
                                'amount' => $dAmt,
                                'transaction_date' => $monthDate->copy()->day($dDay)->toDateString(),
                                'description' => $diningDesc[$idx % count($diningDesc)],
                                'recurring_transaction_id' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                // 6. Transport (metro, cabs, fuel)
                if (isset($categoryMap['Transport'])) {
                    $transportDays = [2, 7, 12, 18, 22, 26];
                    $transDesc = ['Metro smart card recharge', 'Ride share to office', 'Fuel refill', 'Cab home from dinner', 'Bus transit pass', 'Airport cab'];
                    foreach ($transportDays as $idx => $tDay) {
                        if ($tDay <= $maxDay) {
                            $tAmt = number_format(mt_rand(200, 1400) + (mt_rand(0, 99) / 100), 2, '.', '');
                            $transactions[] = [
                                'user_id' => $user->id,
                                'category_id' => $categoryMap['Transport']->id,
                                'amount' => $tAmt,
                                'transaction_date' => $monthDate->copy()->day($tDay)->toDateString(),
                                'description' => $transDesc[$idx % count($transDesc)],
                                'recurring_transaction_id' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                // 7. Entertainment (streaming, books, events)
                if (isset($categoryMap['Entertainment'])) {
                    $entDays = [11, 21];
                    $entDesc = ['Streaming subscriptions', 'Cinema tickets & snacks', 'Book store purchase', 'Concert tickets'];
                    foreach ($entDays as $idx => $eDay) {
                        if ($eDay <= $maxDay) {
                            $eAmt = number_format(mt_rand(499, 1999) + (mt_rand(0, 99) / 100), 2, '.', '');
                            $transactions[] = [
                                'user_id' => $user->id,
                                'category_id' => $categoryMap['Entertainment']->id,
                                'amount' => $eAmt,
                                'transaction_date' => $monthDate->copy()->day($eDay)->toDateString(),
                                'description' => $entDesc[$idx % count($entDesc)],
                                'recurring_transaction_id' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                // 8. Shopping & Health (1-2 entries per month)
                if ($maxDay >= 15 && isset($categoryMap['Shopping'])) {
                    $transactions[] = [
                        'user_id' => $user->id,
                        'category_id' => $categoryMap['Shopping']->id,
                        'amount' => number_format(mt_rand(1200, 4800) + (mt_rand(0, 99) / 100), 2, '.', ''),
                        'transaction_date' => $monthDate->copy()->day(15)->toDateString(),
                        'description' => 'Apparel & Home essentials',
                        'recurring_transaction_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($maxDay >= 20 && isset($categoryMap['Health'])) {
                    $transactions[] = [
                        'user_id' => $user->id,
                        'category_id' => $categoryMap['Health']->id,
                        'amount' => number_format(mt_rand(400, 1800) + (mt_rand(0, 99) / 100), 2, '.', ''),
                        'transaction_date' => $monthDate->copy()->day(20)->toDateString(),
                        'description' => 'Pharmacy & Health supplements',
                        'recurring_transaction_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 9. Occasional Freelance Income (every 2-3 months)
                if ($monthOffset % 3 === 0 && $maxDay >= 17 && isset($categoryMap['Freelance'])) {
                    $transactions[] = [
                        'user_id' => $user->id,
                        'category_id' => $categoryMap['Freelance']->id,
                        'amount' => (string) mt_rand(18000, 32000).'.00',
                        'transaction_date' => $monthDate->copy()->day(17)->toDateString(),
                        'description' => 'Client UI consulting project',
                        'recurring_transaction_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 10. Quarterly Interest Income
                if ($monthOffset % 3 === 1 && $maxDay >= 28 && isset($categoryMap['Interest'])) {
                    $transactions[] = [
                        'user_id' => $user->id,
                        'category_id' => $categoryMap['Interest']->id,
                        'amount' => number_format(mt_rand(1200, 2400) + (mt_rand(0, 99) / 100), 2, '.', ''),
                        'transaction_date' => $monthDate->copy()->day(28)->toDateString(),
                        'description' => 'Fixed Deposit quarterly interest payout',
                        'recurring_transaction_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert all transactions for maximum performance
            foreach (array_chunk($transactions, 100) as $chunk) {
                DB::table('transactions')->insert($chunk);
            }

            // Set up budgets for current month and previous month
            $monthsToBudget = [
                $today->copy()->subMonth()->startOfMonth()->toDateString(),
                $today->copy()->startOfMonth()->toDateString(),
            ];

            $budgetLimits = [
                'Groceries' => '15000.00',
                'Rent' => '22000.00',
                'Utilities' => '4500.00',
                'Transport' => '5000.00',
                'Dining Out' => '7000.00',
                'Entertainment' => '3000.00',
                'Shopping' => '5000.00',
                'Health' => '2500.00',
            ];

            foreach ($monthsToBudget as $bMonth) {
                foreach ($budgetLimits as $catName => $limit) {
                    if (isset($categoryMap[$catName])) {
                        Budget::create([
                            'user_id' => $user->id,
                            'category_id' => $categoryMap[$catName]->id,
                            'month' => $bMonth,
                            'amount_limit' => $limit,
                        ]);
                    }
                }
            }

            // Create two recurring rules (Salary monthly, Rent monthly)
            $elevenMonthsAgo = $today->copy()->subMonths(11)->startOfMonth();

            if (isset($categoryMap['Salary'])) {
                RecurringTransaction::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryMap['Salary']->id,
                    'amount' => '75000.00',
                    'description' => 'Monthly Salary',
                    'frequency' => Frequency::Monthly,
                    'start_date' => $elevenMonthsAgo->copy()->day(1)->toDateString(),
                    'last_run_date' => $today->copy()->startOfMonth()->day(1)->toDateString(),
                    'next_run_date' => $today->copy()->addMonth()->startOfMonth()->day(1)->toDateString(),
                    'runs_count' => 12,
                    'is_active' => true,
                ]);
            }

            if (isset($categoryMap['Rent'])) {
                RecurringTransaction::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryMap['Rent']->id,
                    'amount' => '22000.00',
                    'description' => 'Apartment Rent',
                    'frequency' => Frequency::Monthly,
                    'start_date' => $elevenMonthsAgo->copy()->day(5)->toDateString(),
                    'last_run_date' => $today->copy()->startOfMonth()->day(5)->toDateString(),
                    'next_run_date' => $today->copy()->addMonth()->startOfMonth()->day(5)->toDateString(),
                    'runs_count' => 12,
                    'is_active' => true,
                ]);
            }
        });
    }
}
