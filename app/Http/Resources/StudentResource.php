<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($this->isCurrentUser(), $this->email),
            'role' => $this->role,
            'price' => $this->whenLoaded('studentProfile', $this->studentProfile?->price),
            'start_date' => $this->whenLoaded('studentProfile', $this->studentProfile?->start_date),
            'end_date' => $this->whenLoaded('studentProfile', $this->studentProfile?->end_date),
            'details' => $this->whenLoaded('studentProfile', $this->studentProfile?->details),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    protected function isCurrentUser()
    {
        return auth()->check() && auth()->id() === $this->id;
    }
}