<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KoordinatTelemetri extends Model
{
    protected $table = 'koordinat_telemetri';

    protected $guarded = [];

    public function pos_pengamatan()
    {
        return $this->belongsTo(PosPengamatan::class);
    }
}
