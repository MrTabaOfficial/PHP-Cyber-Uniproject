<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can list every account.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can edit the account's name and email.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can change the account's role.
     *
     * Administrators cannot change their own role, so an administrator can
     * never remove the last administrator by accident.
     */
    public function changeRole(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isNot($model);
    }

    /**
     * Determine whether the user can delete the account.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isNot($model);
    }
}
