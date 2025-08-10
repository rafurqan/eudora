<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use CloudinaryLabs\CloudinaryLaravel\Media\CloudinaryImage;

class FileHelper
{
    public static function saveBase64File(string $base64, string $folder, int $maxSizeMb = 2): string
    {
        $imageData = explode(',', $base64)[1] ?? null;
        if (!$imageData) {
            throw new \Exception("Format base64 tidak valid");
        }

        $decoded = base64_decode($imageData);
        if ($decoded === false) {
            throw new \Exception("Base64 decode gagal");
        }

        $maxSize = $maxSizeMb * 1024 * 1024;
        if (strlen($decoded) > $maxSize) {
            throw new \Exception("Ukuran file melebihi {$maxSizeMb}MB");
        }

        $finfo = finfo_open();
        $mime = finfo_buffer($finfo, $decoded, FILEINFO_MIME_TYPE);
        $allowedMimes = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf',
        ];

        if (!isset($allowedMimes[$mime])) {
            throw new \Exception("Tipe file tidak didukung: $mime");
        }

        $ext = $allowedMimes[$mime];
        $filename = Str::uuid()->toString();
        $fullFilename = $filename . '.' . $ext;

        if (app()->environment('production')) {
            // Simpan file sementara
            $tempPath = sys_get_temp_dir() . '/' . $fullFilename;
            file_put_contents($tempPath, $decoded);

            $uploadResult = Cloudinary::upload($tempPath, [
                'folder' => $folder,
                'public_id' => $filename,
                'resource_type' => $mime === 'application/pdf' ? 'raw' : 'image',
            ]);

            return $uploadResult->getPublicId(); // simpan ini ke database
        } else {
            Storage::disk('public')->put("{$folder}/{$fullFilename}", $decoded);
            return $fullFilename;
        }
    }

    public static function deleteFile(string $folder, string $filename): bool
    {
        if (app()->environment('production')) {
            try {
                Cloudinary::destroy("{$folder}/{$filename}");
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            $path = "{$folder}/{$filename}";
            return Storage::disk('public')->delete($path);
        }
    }

    public static function getFileUrl(string $folder, string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        if (app()->environment('production')) {
            return (new CloudinaryImage("{$folder}/{$filename}"))->toUrl();
        } else {
            return Storage::disk('public')->url("{$folder}/{$filename}");
        }
    }

    public static function fileExists(string $folder, string $filename): bool
    {
        if (app()->environment('production')) {
            return true;
        } else {
            return Storage::disk('public')->exists("{$folder}/{$filename}");
        }
    }
}
