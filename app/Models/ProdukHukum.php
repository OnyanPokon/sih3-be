<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukHukum extends Model
{
    protected $table = 'produk_hukum';

    protected $fillable = [
        'unor_id',
        'user_id',
        'judul',
        'slug',
        'deskripsi',
        'tahun',
        'file',
        'status',
    ];

    public function unor()
    {
        return $this->belongsTo(Unor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
