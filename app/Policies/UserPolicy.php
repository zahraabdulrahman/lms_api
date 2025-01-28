<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function createStudent(User $user)
    {
        return $user->isAdmin();
    }

    public function view(User $authUser, User $targetUser)
    {
        return $authUser->isAdmin() || $authUser->id === $targetUser->id;
    }

    public function update(User $authUser, User $targetUser)
    {
        return $authUser->isAdmin() || $authUser->id === $targetUser->id;
    }

    public function delete(User $authUser, User $targetUser)
    {
        return $authUser->isAdmin();
    }
}