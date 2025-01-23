<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use App\Http\Resources\StudentResource;

class UserController extends Controller
{

    public function store(Request $request){ //creating a new student
        $validate_data = $request->validate([
            'name' => 'required|string|max:250',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'details' => 'nullable|string',
            'instructor_name' => 'required|string|max:250',
        ]); //validate data

        User::create($validate_data); //create the student

        return response()->json(["message" => "Student created successfully"], 201);
    }

    public function update(Request $request, string $id) //updating a student
    {
        $user = User::findOrFail($id); // if not found returns error

        $validated_data = $request->validate(
            [
                'name' => 'required|string|max:250',
                'price' => 'required|numeric',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'details' => 'nullable|string',
                'instructor_name' => 'required|string|max:250',
            ]
        );//validate data
    
        $user->update($validated_data);

        return response()->json(["message" => "Student updated successfully"], 200);
    }

    public function show(string $id)
    {
        $user = user::findOrFail($id); //error returned if not found

        return response()->json(new StudentResource($user), 200);
    }
    
}
