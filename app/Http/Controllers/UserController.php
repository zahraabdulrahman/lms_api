<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Create a new student (admin only)
    public function store(Request $request)
    {
        $this->authorize('create-student', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'details' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'student'
        ]);

        $user->studentProfile()->create([
            'price' => $validated['price'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'details' => $validated['details']
        ]);

        return new StudentResource($user->load('studentProfile'));
    }

    // Update user (admin or own profile)
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:250',
            'email' => ['sometimes','email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'price' => 'sometimes|numeric',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'details' => 'nullable|string',
        ]);

        // Update base user
        $user->update($validated);

        // Update student profile if exists
        if ($user->isStudent() && $user->studentProfile) {
            $user->studentProfile()->update($request->only([
                'price', 'start_date', 'end_date', 'details'
            ]));
        }

        return new StudentResource($user->fresh()->load('studentProfile'));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return new StudentResource($user->load('studentProfile'));
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        if ($user->studentProfile) {
            $user->studentProfile()->delete();
        }
        
        $user->delete();
        
        return response()->noContent();
    }
}