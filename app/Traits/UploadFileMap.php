<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exceptions\InvalidShapefileException;
use Illuminate\Support\Facades\Log;
use Shapefile\ShapefileReader;
use ZipArchive;

trait UploadFileMap
{
    /**
     * Method utama untuk menangani upload file peta.
     * Akan otomatis mendeteksi apakah file adalah ZIP (SHP) atau GeoJSON.
     *
     * @param UploadedFile $file File yang diupload (.zip atau .geojson)
     * @param string $storageFolder Folder target di 'storage/app/public/'
     * @return string Path relatif file GeoJSON yang disimpan
     * @throws InvalidShapefileException
     */
    public function handleMapUpload(UploadedFile $file, string $storageFolder = 'pemetaan'): string
    {
        // 1. Dapatkan ekstensi file
        $extension = strtolower($file->getClientOriginalExtension());

        // 2. Routing logika berdasarkan ekstensi
        if ($extension === 'zip') {
            // Jika .zip, jalankan alur konversi SHP
            return $this->convertZipToGeoJson($file, $storageFolder);
        } elseif ($extension === 'geojson') {
            // Jika .geojson, jalankan alur simpan langsung
            return $this->saveGeoJsonFile($file, $storageFolder);
        } else {
            // Sebenarnya ini sudah ditangani Form Request, tapi sebagai pengaman
            throw new InvalidShapefileException('Format file tidak didukung. Harap unggah .zip atau .geojson.');
        }
    }

    /**
     * Memproses file ZIP Shapefile, mengonversi, dan menyimpannya.
     * (Ini adalah method lama Anda, sekarang dibuat private/protected)
     *
     * @return string Path relatif
     */
    private function convertZipToGeoJson(UploadedFile $zipFile, string $storageFolder): string
    {
        // 1. Buat direktori temporary
        $tempDir = storage_path('app/temp/' . Str::uuid());
        File::makeDirectory($tempDir, 0755, true);

        // 2. Ekstrak file zip
        $zip = new ZipArchive;
        if ($zip->open($zipFile->getRealPath()) !== TRUE) {
            File::deleteDirectory($tempDir);
            throw new InvalidShapefileException('Gagal membuka atau mengekstrak file ZIP.');
        }
        $zip->extractTo($tempDir);
        $zip->close();

        // 3. Cari komponen
        $filePaths = $this->findShapefileComponents($tempDir);

        // 4. Validasi komponen
        if (!$filePaths['shp'] || !$filePaths['shx'] || !$filePaths['dbf']) {
            File::deleteDirectory($tempDir);
            throw new InvalidShapefileException('Komponen wajib (.shp, .shx, .dbf) tidak ditemukan di dalam ZIP.');
        }

        // 5. Konversi
        try {
            $geojsonArray = $this->convertShpToArray($filePaths); // Menggunakan helper
            $geojsonString = json_encode($geojsonArray, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            File::deleteDirectory($tempDir);
            throw new InvalidShapefileException('Gagal membaca file Shapefile: ' . $e->getMessage());
        }

        $baseName = pathinfo($filePaths['shp'], PATHINFO_FILENAME);
        $geojsonFileName = Str::slug($baseName) . '_' . time() . '.geojson';
        $storagePath = "{$storageFolder}/{$geojsonFileName}";
        Storage::disk('public')->put($storagePath, $geojsonString);

        File::deleteDirectory($tempDir);

        return "{$storageFolder}/{$geojsonFileName}";
    }

    /**
     * Memvalidasi dan menyimpan file GeoJSON yang diupload langsung.
     *
     * @return string Path relatif
     */
    private function saveGeoJsonFile(UploadedFile $geojsonFile, string $storageFolder): string
    {
        // 1. Baca konten file
        $content = $geojsonFile->get();

        // 2. Validasi apakah ini JSON yang valid
        $data = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidShapefileException('File .geojson tidak valid (format JSON rusak).');
        }

        // 3. (Opsional tapi disarankan) Validasi struktur dasar GeoJSON
        if (empty($data->type) || !in_array($data->type, ['FeatureCollection', 'Feature'])) {
            throw new InvalidShapefileException('File .geojson tidak valid (bukan FeatureCollection atau Feature).');
        }

        // 4. Tentukan nama dan path output
        $baseName = pathinfo($geojsonFile->getClientOriginalName(), PATHINFO_FILENAME);
        $geojsonFileName = Str::slug($baseName) . '_' . time() . '.geojson';

        $storagePath = "{$storageFolder}/{$geojsonFileName}";
        Storage::disk('public')->put($storagePath, $content);

        return "{$storageFolder}/{$geojsonFileName}";
    }

    /**
     * Helper untuk konversi SHP ke Array GeoJSON.
     * (Ini adalah bagian inti dari method lama Anda)
     */
    private function convertShpToArray(array $paths): array
    {
        $dbfEncoding = 'UTF-8';
        if ($paths['cpg'] && File::exists($paths['cpg'])) {
            $dbfEncoding = trim(file_get_contents($paths['cpg']));
        }

        $shapefile = new ShapefileReader($paths['shp'], ['dbf_encoding' => $dbfEncoding]);
        $geojson = ['type' => 'FeatureCollection', 'features' => []];

        while ($record = $shapefile->fetchRecord()) {
            if ($record->isDeleted()) continue;
            $geojson['features'][] = [
                'type' => 'Feature',
                'geometry' => json_decode($record->getGeoJSON()),
                'properties' => $record->getDataArray(),
            ];
        }
        unset($shapefile);
        gc_collect_cycles();
        return $geojson;
    }

    /**
     * Helper untuk mencari komponen SHP.
     * (Tidak ada perubahan dari kode sebelumnya)
     */
    private function findShapefileComponents(string $directory): array
    {
        $paths = ['shp' => null, 'shx' => null, 'dbf' => null, 'prj' => null, 'cpg' => null];
        $files = File::glob("{$directory}/*", GLOB_BRACE);
        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($extension === 'shp' && !$paths['shp']) $paths['shp'] = $file;
            elseif ($extension === 'shx' && !$paths['shx']) $paths['shx'] = $file;
            elseif ($extension === 'dbf' && !$paths['dbf']) $paths['dbf'] = $file;
            elseif ($extension === 'prj' && !$paths['prj']) $paths['prj'] = $file;
            elseif ($extension === 'cpg' && !$paths['cpg']) $paths['cpg'] = $file;
        }
        return $paths;
    }

    public function deleteMapFile(?string $filePath): bool
    {
        if (empty($filePath)) {
            return true;
        }
        $fullStoragePath = "public/{$filePath}";

        if (Storage::disk('public')->exists($filePath)) {
            try {
                return Storage::disk('public')->delete($filePath);
            } catch (\Exception $e) {
                Log::error("Gagal menghapus file di storage public: {$filePath} - " . $e->getMessage());
                return false;
            }
        }

        return true;
    }
}
