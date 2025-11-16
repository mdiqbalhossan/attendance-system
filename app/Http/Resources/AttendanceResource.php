<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'student' => $this->when($this->relationLoaded('student'), function () {
                return new StudentResource($this->student);
            }),
            'date' => $this->date->format('Y-m-d'),
            'date_formatted' => $this->date->format('F d, Y'),
            'status' => $this->status,
            'note' => $this->note,
            'recorded_by' => $this->recorded_by,
            'recorder' => $this->when($this->relationLoaded('recordedBy'), function () {
                return [
                    'id' => $this->recordedBy->id,
                    'name' => $this->recordedBy->name,
                    'email' => $this->recordedBy->email,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
