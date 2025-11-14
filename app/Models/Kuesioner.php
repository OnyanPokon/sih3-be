<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuesioner extends Model
{
    protected $guarded = [];
    protected $table = 'kuesioner';

    public function artikel()
    {
        return $this->belongsTo(Artikel::class);
    }
}
