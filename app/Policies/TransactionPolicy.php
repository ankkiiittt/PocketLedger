<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any transactions.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): Response
    {
        return $user->id === $transaction->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create transactions.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the transaction.
     */
    public function update(User $user, Transaction $transaction): Response
    {
        return $user->id === $transaction->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the transaction.
     */
    public function delete(User $user, Transaction $transaction): Response
    {
        return $user->id === $transaction->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
