<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'comment' => $this->comment,
            'course' => [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'start_date' => $this->course->start_date,
                'end_date' => $this->course->end_date,
            ],
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->name,
            ],
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
