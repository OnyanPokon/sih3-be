<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahSungai extends Model
{
    protected $table = 'wilayah_sungai';

    protected $guarded = [];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function unor()
    {
        return $this->belongsTo(Unor::class);
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, 'referensi');
    }
}

