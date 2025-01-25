<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Course::class); // Authorize creating any course

        $validated_data = $request->validate([
            'title' => 'required|string|max:250',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'details' => 'nullable|string',
            'instructor_name' => 'required|string|max:250',
        ]);

        $course = Course::create($validated_data);

        return response()->json(['message' => "Course created successfully", 'course' => $course], 201);
    }

    public function update(Request $request, Course $course) // Type-hint the Course model
    {
        $this->authorize('update', $course); // Correct authorization

        $validated_data = $request->validate([
            'title' => 'nullable|string|max:250',
            'price' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'details' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:250',
        ]);

        $course->update($validated_data);

        return response()->json(['message' => "Updated successfully", 'course' => $course], 200);
    }

    public function index(Request $request)
    {
        $query = Course::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('start_date')) {
            $query->whereDate('start_date', '=', $request->start_date);
        }

        if ($request->has('instructor_name')) {
            $query->where('instructor_name', 'like', '%' . $request->instructor_name . '%');
        }

        $courses = $query->paginate(10);

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'No courses found'], 404);
        }

        return CourseResource::collection($courses);
    }

    public function show(Course $course) // Route Model Binding
    {
        return response()->json(new CourseResource($course), 200);
    }

    public function destroy(Course $course) // Type-hint the Course model
    {
        $this->authorize('delete', $course); // Correct authorization

        $course->delete();

        return response()->json(['message' => "Course deleted successfully"], 200);
    }
}