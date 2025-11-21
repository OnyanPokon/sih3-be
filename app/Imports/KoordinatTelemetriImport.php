<?php

namespace App\Imports;

use App\Models\KoordinatTelemetri;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class KoordinatTelemetriImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected $posPengamatanId;

    /**
     * Konstruktor untuk menerima pos_pengamatan_id dari controller/service.
     * * @param int $posPengamatanId ID Pos Pengamatan yang valid.
     */
    public function __construct(int $posPengamatanId)
    {
        $this->posPengamatanId = $posPengamatanId;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $dataToInsert = [];
        $posPengamatanId = $this->posPengamatanId;

        foreach ($collection as $row) {

            $tanggalExcel = $row['tanggal'] ?? $row['Tanggal'] ?? null;
            $waktuExcel = $row['waktu'] ?? $row['Waktu'] ?? 0;

            if (empty($tanggalExcel)) {
                continue;
            }

            // 1. Konversi TANGGAL (dari serial number penuh)
            $tanggalDB = null;
            if (is_numeric($tanggalExcel) && $tanggalExcel > 25569) {
                // Hanya konversi bagian tanggal, format sebagai YYYY-MM-DD
                $tanggalDB = Carbon::createFromTimestamp(ExcelDate::excelToTimestamp($tanggalExcel))
                    ->toDateString(); // Menggunakan toDateString()
            } else {
                // Fallback jika berupa string
                try {
                    $tanggalDB = Carbon::parse($tanggalExcel)->toDateString();
                } catch (\Exception $e) {
                    continue;
                }
            }

            // 2. Konversi WAKTU (dari desimal)
            $waktuDB = null;
            if (is_numeric($waktuExcel)) {
                // Excel menyimpan waktu sebagai desimal hari (misal 0.5 = 12:00:00)
                // Konversi ke format H:i:s
                $seconds = round($waktuExcel * 86400); // 86400 detik dalam sehari
                $waktuDB = gmdate('H:i:s', $seconds);
            } else {
                // Fallback jika berupa string
                $waktuDB = $waktuExcel; // Anggap format H:i:s sudah benar
            }

            $dataToInsert[] = [
                'pos_pengamatan_id' => $posPengamatanId,
                'nilai'             => $row['nilai'] ?? null,
                'waktu'             => $waktuDB ?? null,
                'tanggal'           => $tanggalDB ?? null,
                'warna'             => $row['warna'] ?? null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        // Lakukan mass insert ke database
        if (!empty($dataToInsert)) {
            KoordinatTelemetri::insert($dataToInsert);
        }
    }

    /**
     * Memungkinkan batch insert (lebih cepat).
     * @return int
     */
    public function batchSize(): int
    {
        return 500; // Jumlah baris yang akan dimasukkan dalam satu kali query
    }

    /**
     * Memungkinkan pemrosesan data dalam bentuk chunk (menghemat memori).
     * @return int
     */
    public function chunkSize(): int
    {
        return 500; // Jumlah baris yang akan dibaca ke memori
    }
}
