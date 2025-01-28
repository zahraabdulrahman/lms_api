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

        return new CourseResource($course);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated_data = $request->validate([
            'title' => 'required|string|max:250',
            'price' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'details' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:250',
        ]);

        $course->update($validated_data);

        return new CourseResource($course);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Course::class);

        $query = Course::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%'.$request->title.'%');
        }

        if ($request->has('start_date')) {
            $query->whereDate('start_date', '=', $request->start_date);
        }

        if ($request->has('instructor_name')) {
            $query->where('instructor_name', 'like', '%'.$request->instructor_name.'%');
        }

        $courses = $query->paginate(10);

        return CourseResource::collection($courses);
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);

        return new CourseResource($course);
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        // authroize before allowing deletion
        $course->delete();

        return response()->noContent();
    }
}
