<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->getRoleNames()[0],
            'unor' => $this->personil->unor->only('nama', 'wilayah'),
            'permissions' => $this->getPermissionsViaRoles()->pluck('name'),
        ];
    }
}
