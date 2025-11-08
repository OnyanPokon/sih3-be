<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemetaan extends Model
{
    protected $guarded = [];

    protected $table = 'pemetaan';

    protected $casts = [
        'koordinat' => 'array',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function unor()
    {
        return $this->belongsTo(Unor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
