<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonilResource extends JsonResource
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
            'user_id' => [
                'id' => $this->user_id,
                'email' => $this->user->email,
                'username' => $this->user->username,
                'role' => $this->user->getRoleNames()[0],
            ],
            'unor_id' => $this->unor,
            'nama' => $this->nama,
            'nip' => $this->nip,
            'jabatan' => $this->jabatan,
            'no_hp' => $this->no_hp,
            'status' => $this->status,
        ];
    }
}
