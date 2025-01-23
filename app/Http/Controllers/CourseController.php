<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\CourseResource;

class CourseController extends Controller
{

    public function store(Request $request) //to create a new course
    {
        $validated_data = $request->validate([
            'title' => 'required|string|max:250',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'details' => 'nullable|string',
            'instructor_name' => 'required|string|nax:250'
        ]); //validating the data

        Course::create($validated_data); //creation of the course

        return response()->json(['message' => "Course created successfully"], 201);
    }

    public function update(Request $request, string $id) //updating a course
    {
        $course = Course::findOrFail($id); //finding course, if not found return error

        $validated_data = $request->validate([
            'title' => 'required|string|max:250',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'details' => 'nullable|string',
            'instructor_name' => 'required|string|max:250'
        ]);//validate data

        $course->update($validated_data); //update the course w/ validated data

        return response()->json(['message' => "Updated successfully"], 200);
    }

    public function index(Request $request)
    {
        $query = Course::query(); //building a query

        if($request->has('title'))//if nthe search is by title
        {
            $query->where('title', 'like', '%' . $request->title . '%'); 
        }

        if($request->has('start_date'))//if the search has starting date
        {
            $query->whereDate('start_date', '=', $request->start_date); 
        }

        if($request->has('instructor_name'))//if the search has instructor name
        {
            $query->where('instructor_name', 'like', '%' . $request->instructor_name . '%'); 
        }
        
        $courses = $query->paginate(10);

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'No courses found'], 404); //in case the query returns empty
        }

        return CourseResource::collection($courses);
    }

    public function show(string $id)// for showing a specifc course
    {
        $course = Course::findOrFail($id); //if course not found, it returns an error message

        return response()->json(new CourseResource($course), 200);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id); //error returned if not found
        $course->delete();

        return response()->json(['message' => "Course deleted successfully"], 200);
    }

}
