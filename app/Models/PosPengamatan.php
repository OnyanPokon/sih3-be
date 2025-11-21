<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosPengamatan extends Model
{
    protected $table = 'pos_pengamatan';

    protected $guarded = [];

    public function jenis_pos()
    {
        return $this->belongsTo(JenisPos::class);
    }

    public function koordinat_telemetri()
    {
        return $this->hasMany(KoordinatTelemetri::class);
    }
}
