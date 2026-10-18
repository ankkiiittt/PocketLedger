<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecurringTransactionPolicy
{
    /**
     * Determine whether the user can view any recurring transactions.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the recurring transaction.
     */
    public function view(User $user, RecurringTransaction $recurringTransaction): Response
    {
        return $user->id === $recurringTransaction->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create recurring transactions.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the recurring transaction.
     */
    public function update(User $user, RecurringTransaction $recurringTransaction): Response
    {
        return $user->id === $recurringTransaction->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the recurring transaction.
     */
    public function delete(User $user, RecurringTransaction $recurringTransaction): Response
    {
        return $user->id === $recurringTransaction->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
