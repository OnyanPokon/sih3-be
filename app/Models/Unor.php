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
}
