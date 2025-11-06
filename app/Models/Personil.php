<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personil extends Model
{
    protected $table = 'personil';

    protected $fillable = [
        'user_id',
        'unor_id',
        'nip',
        'nama',
        'no_hp',
        'jabatan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unor()
    {
        return $this->belongsTo(Unor::class);
    }
}
