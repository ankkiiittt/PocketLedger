<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any categories.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the category.
     */
    public function view(User $user, Category $category): Response
    {
        return $user->id === $category->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, Category $category): Response
    {
        return $user->id === $category->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, Category $category): Response
    {
        return $user->id === $category->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can archive the category.
     */
    public function archive(User $user, Category $category): Response
    {
        return $this->update($user, $category);
    }

    /**
     * Determine whether the user can restore the category.
     */
    public function restore(User $user, Category $category): Response
    {
        return $this->update($user, $category);
    }
}
