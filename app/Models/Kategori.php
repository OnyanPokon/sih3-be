<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $guarded = [];

    protected $table = 'kategori';

    public function pemetaan()
    {
        return $this->hasMany(Pemetaan::class);
    }

    public function artikel()
    {
        return $this->hasMany(Artikel::class);
    }

    public function berita()
    {
        return $this->hasMany(Berita::class);
    }
}
