<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WilayahSungaiResource extends JsonResource
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
            'kategori_id' => $this->kategori_id,
            'kategori' => KategoriResource::make($this->whenLoaded('kategori')),
            'unor_id' => $this->unor_id,
            'unor' => UnorResource::make($this->whenLoaded('unor')),
            'kewenangan' => $this->kewenangan,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'thumbnail' => $this->thumbnail,
            'luas_wilayah' => $this->luas_wilayah,
            'total_titik_pantau' => $this->total_titik_pantau,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
