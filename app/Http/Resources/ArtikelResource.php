<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtikelResource extends JsonResource
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
            'kategori_id' => $this->kategori,
            'judul' => $this->judul,
            'slug' => $this->slug,
            'konten' => $this->konten,
            'status' => $this->status,
            'tanggal_publikasi' => $this->tanggal_publikasi,
            'unor_id' => $this->unor,
            'user_id' => $this->user,
            'thumbnails' => $this->thumbnails,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
