<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAccount
{
    /**
     * Delete user and all associated records in explicit order to avoid RESTRICT conflicts.
     */
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            $userId = $user->id;

            // 1. Transactions
            DB::table('transactions')->where('user_id', $userId)->delete();

            // 2. Recurring rules
            DB::table('recurring_transactions')->where('user_id', $userId)->delete();

            // 3. Budgets
            DB::table('budgets')->where('user_id', $userId)->delete();

            // 4. Categories
            DB::table('categories')->where('user_id', $userId)->delete();

            // 5. Sessions
            DB::table('sessions')->where('user_id', $userId)->delete();

            // 6. User account
            $user->delete();
        });
    }
}
