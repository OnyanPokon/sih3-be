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
            'role' => $this->getRoleNames()->first(),

            'unor' => [
                'id' => $this->personil?->unor?->id,
                'nama' => $this->personil?->unor?->nama,
                'wilayah' => $this->personil?->unor?->wilayah,
                'kategoris' => $this->personil?->unor?->kategoris->map(function ($kategori) {
                    return [
                        'id' => $kategori->id,
                        'nama' => $kategori->nama_kategori,
                    ];
                }),
            ],

            'permissions' => $this->getPermissionsViaRoles()->pluck('name'),
        ];
    }
}
