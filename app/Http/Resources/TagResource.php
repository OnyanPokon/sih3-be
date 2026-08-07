<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $referensiTypeAlias = match ($this->referensi_type) {
            'App\Models\WilayahSungai' => 'wilayah_sungai',
            default => $this->referensi_type,
        };

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                ];
            }),
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'warna' => $this->warna,
            'icon' => $this->icon,
            'kategori_id' => $this->kategori_id,
            'kategori' => $this->whenLoaded('kategori', function () {
                return KategoriResource::make($this->kategori);
            }),
            'referensi_type' => $referensiTypeAlias,
            'referensi_id' => $this->referensi_id,
            'referensi' => $this->whenLoaded('referensi', function () {
                if ($this->referensi instanceof \App\Models\WilayahSungai) {
                    return WilayahSungaiResource::make($this->referensi);
                }
                return $this->referensi;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
