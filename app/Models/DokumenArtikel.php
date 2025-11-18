<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenArtikel extends Model
{
    protected $table = 'dokumen_artikel';

    protected $fillable = [
        'artikel_id',
        'nama_dokumen',
        'dokumen',
    ];

    public function artikel()
    {
        return $this->belongsTo(Artikel::class);
    }
}
