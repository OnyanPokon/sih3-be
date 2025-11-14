<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KuesionerResource extends JsonResource
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
            'artikel_id' => $this->artikel,
            'nama' => $this->nama,
            'instansi' => $this->instansi,
            'asal' => $this->asal,
            'deskripsi' => $this->deskripsi,
            'nilai' => $this->nilai,
            'created_at' => $this->created_at,
        ];
    }
}
