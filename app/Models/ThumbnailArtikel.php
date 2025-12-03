<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThumbnailArtikel extends Model
{
    protected $table = 'thumbnail_artikel';

    protected $fillable = [
        'artikel_id',
        'thumbnail',
    ];

    public function artikel()
    {
        return $this->belongsTo(Artikel::class);
    }
}
