<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PemetaanResource extends JsonResource
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
            'nama_pemetaan' => $this->nama_pemetaan,
            'deskripsi' => $this->deskripsi,
            'tipe' => $this->tipe,
            'jenis' => $this->jenis,
            'file' => $this->file,
            'marker' => $this->marker,
            'koordinat' => $this->koordinat,
            'status' => $this->status,
            'kategori_id' => $this->kategori,
            'unor_id' => $this->unor,
            'user_id' => $this->user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
