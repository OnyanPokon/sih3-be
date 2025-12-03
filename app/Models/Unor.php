<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unor extends Model
{
    protected $table = 'unor';

    protected $fillable = [
        'nama',
        'wilayah',
        'alamat',
        'email',
        'logo',
        'website',
        'no_hp',
    ];

    public function personil()
    {
        return $this->hasMany(Personil::class);
    }

    public function pemetaan()
    {
        return $this->hasMany(Pemetaan::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function artikel()
    {
        return $this->hasMany(Artikel::class);
    }

    public function produk_hukum()
    {
        return $this->hasMany(ProdukHukum::class);
    }

    public function berita()
    {
        return $this->hasMany(Berita::class);
    }
}
