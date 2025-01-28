<?php

namespace App\Http\Controllers;

use App\Http\Resources\RegistrationResource;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Registration::class);

        $query = Registration::query()->with(['user', 'course']);

        // Automatically filter to the student's own registrations if no user_id is provided
        if ($request->user()->role === 'student' && ! $request->has('user_id')) {
            $query->where('user_id', $request->user()->id);
        }

        $query->filter($request->only(['user_id', 'course_id', 'instructor_name']));

        $registrations = $query->paginate(10);

        return RegistrationResource::collection($registrations);
    }

    public function show(Registration $registeration) // show a specific registration
    {
        $this->authorize('view', $registration);

        $registration->load(['user', 'course']);

        return RegistrationResource($registration);
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('create', [Registration::class, $course]);

        if ($course->end_date < now()) {
            return response()->json(['message' => 'Course has already ended'], Response::HTTP_BAD_REQUEST);
        }

        $user = Auth::user();

        if (Registration::where('user_id', $user->id)->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'You are already registered for this course.'], Response::HTTP_BAD_REQUEST); // Use constant
        }

        $registration = Registration::create([
            'user_id' => $user->id, // Use authenticated user's ID
            'course_id' => $course->id, // Use injected Course model's ID
        ]);

        return new RegistrationResource($registration);
    }

    public function update(Request $request, Registration $registration)
    {
        $this->authorize('update', $registration);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::find($validated['course_id']);
        if ($course && $course->end_date < now()) { // Check if course exists before accessing end_date
            return response()->json(['message' => 'The course has already ended. You cannot update the registration.'], 400);
        }

        $registration->update($validated);

        return new RegistrationResource($registration);
    }
}
