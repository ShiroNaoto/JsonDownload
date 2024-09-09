<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'json_keys' => $this->json_keys,
            'json_id' => $this->json_id,
            'contains' => $this->contains
        ];
    }
}
