<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->role === 'student'; // Only students can create comments
    }

    public function viewAny(User $user)
    {
        return in_array($user->role, ['student', 'admin', 'instructor']);
    }

    public function view(User $user, Comment $comment)
    {
        return in_array($user->role, ['student', 'admin', 'instructor']) && $comment->user_id === $user->id;
    }

    public function update(User $user, Comment $comment)
    {
        return $user->role === 'admin' || $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment)
    {
        return $user->role === 'admin' || $user->id === $comment->user_id;
    }
}
