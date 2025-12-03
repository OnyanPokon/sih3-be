<?php

namespace App\Imports;

use App\Models\JenisPos;
use App\Models\Kategori;
use App\Models\PosPengamatan;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PosPengamatanImport implements ToCollection, WithStartRow
{
    protected $kategoriId;

    public function __construct(int $kategoriId)
    {
        $this->kategoriId = $kategoriId;
    }

    public function startRow(): int
    {
        return 3;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $kategori_id = $this->kategoriId;

        foreach ($collection as $row) {

            if (empty($row[0]) || empty($row[1])) {
                continue;
            }

            $nama_jenis_pos_mentah = trim($row[0]);
            $nama_pos = $row[1];
            $koordinat = $row[2] ?? null; // Koordinat mungkin opsional
            $alamat = $row[3] ?? null;

            $nama_jenis_pos_normalisasi = strtolower($nama_jenis_pos_mentah);

            $jenisPos = JenisPos::where(DB::raw('LOWER(nama)'), $nama_jenis_pos_normalisasi)
                ->where('kategori_id', $kategori_id)
                ->first();

            $jenis_pos_id = null;

            if ($jenisPos) {
                $jenis_pos_id = $jenisPos->id;
            } else {
                $newJenisPos = JenisPos::create([
                    'nama' => $nama_jenis_pos_mentah,
                    'kategori_id' => $kategori_id,
                    'user_id' => Auth::user()->id,
                    'slug' => str()->slug($nama_jenis_pos_mentah),
                    'status' => 'publikasi',
                    'unor_id' => Auth::user()->personil->unor->id,
                    'kewenangan' => '-'
                ]);
                $jenis_pos_id = $newJenisPos->id;
            }

            Log::info("Jenis Pos ID: " . $jenis_pos_id);
            Log::info("Nama Pos: " . $row[0]);

            if ($jenis_pos_id) {
                PosPengamatan::create([
                    'jenis_pos_id' => $jenis_pos_id,
                    'nama' => $nama_pos,
                    'koordinat' => $koordinat,
                    'alamat' => $alamat,
                ]);
            }
        }
    }
}
