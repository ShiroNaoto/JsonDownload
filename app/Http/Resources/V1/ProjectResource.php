<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'user_id' => $this->user_id,
            'staffname' => $this->staffname,
            'projectname' => $this->projectname,
            'description' => $this->description,   
            'created' => $this->created_at,
            'updated' => $this->updated_at
        ];
    }
}
