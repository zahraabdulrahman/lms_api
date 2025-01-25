<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function delete(User $authUser, User $userToDelete)
    {
        return $authUser->role === 'admin' && $userToDelete->role === 'student';
    } //only admins can delete students
}
