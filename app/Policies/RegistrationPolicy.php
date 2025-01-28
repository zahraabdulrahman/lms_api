<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Admins/instructors can view all registrations
        if (in_array($user->role, ['admin', 'instructor'])) {
            return true;
        }

        // Students can only view their own registrations
        return $user->role === 'student' && (
            // Allow if no user_id is specified
            ! request()->has('user_id') ||
            // Allow if user_id matches their own ID
            request('user_id') == $user->id
        );
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
