<?php

namespace App\Models;

use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use HasTags;

    protected $table = 'berita';

    protected $guarded = [];

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
