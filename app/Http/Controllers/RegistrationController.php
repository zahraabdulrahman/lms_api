<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\RegistrationResource;
use App\Models\Course;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RegistrationController extends Controller implements HasMiddleware
{

    public static function middleware(){
        return [
            new Middleware('auth:sanctum')
        ];
    }
    public function index(Request $request){
        $query = Registration::query();

        //filtering by student id if the request has it
        if($request->has('user_id')){
            $query->where('user_id', $request->user_id);
        }

        //filtering by course id if the request has it
        if($request->has('course_id')){
            $query->where('course_id', $request->course_id);
        }

        //fliter by instructor name 
        if ($request->has('instructor_name')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('instructor_name', 'like', '%' . $request->instructor_name . '%');
            });
        }
        
        $registrations = $query->paginate(10);

        return RegistrationResource::collection($registrations);
    }

    public function show($id) //show a specific registration
    {
        $registration = Registration::findOrFail($id);

        return RegistrationResource($registration);
    }

    public function store(Request $request) //creating a registration
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', //checking if it exists in the students table 
            'course_id' => 'required|exists:courses,id', //checking if it exists in courses table
        ]);

        //checking if the course has ended
        $course = Course::findOrFail($validated['course_id']);
        if ($course->end_date < now()){
            return response()->json(['message'=>'Course has already ended'], 400);
        }

        //checking if the student is already registered to the course
        $registration_exists = Registration::where('user_id', $validated['user_id'])->where('course_id', $validated['course_id'])->exists();
        
        if ($registration_exists){
            return response()->json(['message' => 'The student is already registered for this course.'], 400);
        }

        $registration = $request->user()->registrations()->Registration::create([
            'user_id' => $validated['user_id'],
            'course_id' => $validated['course_id'],
        ]); //only auth students(users for now) can create registerations

        return response()->json(['message'=>'Registration created successfully'], 201);
    }

    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id', //checking if it exists in courses table
            'user_id' => 'required|exists:users,id',
        ]);

        $registration = Registration::findOrFail($id); //if not found returns error

        //checking if the course has ended
        $course = Course::findOrFail($validated['course_id']);
        if ($course->end_date < now()) {
            return response()->json(['message' => 'The course has already ended. You cannot update the registration.'], 400);
        } //if it has ended the student can't register it

        //updating
        $registration->course_id = $validated['course_id'];
        $registration->user_id = $validated['user_id'];
        $registration->save();

        return response()->json(['message'=>'Registration updated successfully'], 200);
    }

}
