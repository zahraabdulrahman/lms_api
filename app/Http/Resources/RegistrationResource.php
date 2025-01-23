<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'course_id' => $this->course_id,
            'student_name' => $this->student->name, // Assuming you have a 'name' field on the Student model
            'course_title' => $this->course->title, // Assuming you have a 'title' field on the Course model
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
