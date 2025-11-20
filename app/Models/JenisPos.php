<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPos extends Model
{
    protected $guarded = [];

    protected $table = 'pos_pengamatan';

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

    public function pos_pengamatan()
    {
        return $this->hasMany(PosPengamatan::class, 'pos_pengamatan_id');
    }
}
