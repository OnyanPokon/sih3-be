<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $guarded = [];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function unor()
    {
        return $this->belongsTo(Unor::class, 'unor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thumbnails()
    {
        return $this->hasMany(ThumbnailArtikel::class, 'artikel_id');
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenArtikel::class, 'artikel_id');
    }

    public function kuesioner()
    {
        return $this->hasMany(Kuesioner::class, 'artikel_id');
    }
}
