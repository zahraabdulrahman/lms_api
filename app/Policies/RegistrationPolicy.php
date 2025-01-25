<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationPolicy
{
    
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'instructor']); // Only admin or instructor can view all registrations
    }

    public function view(User $user, Registration $registration)
    {
        return in_array($user->role, ['admin', 'instructor']) || $registration->user_id === $user->id; // Admin or Instructor or the student of the registration can view
    }

    public function create(User $user, $course_id)
    {
        return $user->role === 'student'; // Only student can create
    }

    public function update(User $user, Registration $registration)
    {
        return $registration->user_id === $user->id; // Only the student of the registration can update
    }
}
