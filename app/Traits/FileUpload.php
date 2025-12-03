<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Helpers\ConvertImage;
use Exception;

trait FileUpload
{

    public function uploadPhoto($file, string $folder = 'uploads', string $disk = 'public'): string
    {
        return $file->store($folder, $disk);
    }

    public function uploadPhotoAndConvertToWebP($file, string $folder = 'uploads', string $disk = 'public'): string
    {
        $filePath = $file->store($folder, $disk);
        $extension = $file->getClientOriginalExtension();
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            throw new \Exception("File extension not allowed.");
        }


        if (strtolower($extension) !== 'webp') {
            $webpPath = storage_path("app/{$disk}/" . $folder . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.webp');
            ConvertImage::convertImageToWebP(storage_path("app/{$disk}/" . $filePath), $webpPath);

            Storage::disk($disk)->delete($filePath);

            $filePath = "{$folder}/" . pathinfo($webpPath, PATHINFO_BASENAME);
        }

        return $filePath;
    }

    public function unlinkPhoto(?string $filePath, string $disk = 'public'): bool
    {
        if (empty($filePath)) {
            return true;
        }

        return Storage::disk($disk)->delete($filePath);
    }

    public function updatePhoto($file, ?string $oldFilePath, string $folder = 'uploads', string $disk = 'public'): string
    {
        // Hapus file lama jika ada
        if ($oldFilePath && Storage::disk($disk)->exists($oldFilePath)) {
            Storage::disk($disk)->delete($oldFilePath);
        }

        // Upload file baru
        return $this->uploadPhoto($file, $folder, $disk);
    }

    public function uploadDocument($file, array $allowedExtensions = ['geojson', 'png', 'pdf'], string $folder = 'documents', string $disk = 'public'): string
    {
        $extension = $file->getClientOriginalExtension();

        if (!in_array(strtolower($extension), $allowedExtensions)) {
            throw new \Exception("Ekstensi file tidak diizinkan.");
        }

        // Simpan file dengan ekstensi asli
        $fileName = Str::random(20) . '-' . time(); // Buatkan nama file yang random
        $fileNameWithExtension = $fileName . '.' . $extension; // Tambahkan ekstensi asli

        return $file->storeAs($folder, $fileNameWithExtension, $disk);
    }
}
