<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'user_id',
        'nama',
        'deskripsi',
        'referensi_id',
        'referensi_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }

    public function taggables(): HasMany
    {
        return $this->hasMany(Taggable::class, 'tag_id');
    }
}

