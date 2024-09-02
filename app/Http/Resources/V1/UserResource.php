<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Passport\Token;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hasActiveToken = $this->tokens()->where('revoked', false)->exists();
        $hasToken = $this->tokens()->exists();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->acctype,   
            'created' => $this->created_at,
            'updated' => $this->updated_at,
            'token_status' => $hasActiveToken ? 'Active' : 'Inactive',
            'has_token' => $hasToken ? 'Yes' : 'No'
        ];
    }
}
