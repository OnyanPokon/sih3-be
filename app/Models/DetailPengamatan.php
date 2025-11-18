<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPengamatan extends Model
{
    protected $table = 'detail_pengamatan';

    protected $guarded = [];

    public function pos_pengamatan()
    {
        return $this->belongsTo(PosPengamatan::class);
    }
}
