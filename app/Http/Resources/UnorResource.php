<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnorResource extends JsonResource
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
            'wilayah' => $this->wilayah,
            'alamat' => $this->alamat,
            'email' => $this->email,
            'logo' => $this->logo,
            'website' => $this->website,
            'no_hp' => $this->no_hp,
        ];
    }
}
