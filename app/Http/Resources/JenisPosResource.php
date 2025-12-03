<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JenisPosResource extends JsonResource
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
            'nama' => $this->nama,
            'slug' => $this->slug,
            'kewenangan' => $this->kewenangan,
            'status' => $this->status,
            'unor_id' => $this->unor,
            'user_id' => $this->user,
            'kategori_id' => $this->kategori,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
