<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

     //only admin and instructor can update the course
    public function update(User $user, Course $course)
    {
        return in_array($user->role, ['admin', 'instructor']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'instructor']); // Only admin and instructor
    }
  
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin'; // Only admin can delete
    }

}
