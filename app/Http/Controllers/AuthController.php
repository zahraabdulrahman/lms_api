<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ]); // validate users password and email

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials']);
        }

        // create token
        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'price' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'details' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'role' => 'required|in:student,instructor,admin',
        ]);

        $validated['password'] = bcrypt($validated['password']); // crypt the password

        $user = User::create($validated);
        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json(['token' => $token], 201); // Return the token and the user
    }

    public function logout(Request $request)// to delete created token
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'logged out successfully',
        ]);
    }
}
