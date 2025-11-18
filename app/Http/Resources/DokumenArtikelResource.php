<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DokumenArtikelResource extends JsonResource
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
            'nama_dokumen' => $this->nama_dokumen,
            'dokumen' => $this->dokumen,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
