<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Simpan file base64 ke storage/public/{folder}
     *
     * @throws \Exception
     */
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

        // Validasi ukuran maksimal
        $maxSize = $maxSizeMb * 1024 * 1024; // dalam byte
        if (strlen($decoded) > $maxSize) {
            throw new \Exception("Ukuran file melebihi {$maxSizeMb}MB");
        }

        // Deteksi MIME
        $finfo = finfo_open();
        $mime = finfo_buffer($finfo, $decoded, FILEINFO_MIME_TYPE);

        // Validasi tipe yang diizinkan
        $allowedMimes = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf',
        ];

        if (!isset($allowedMimes[$mime])) {
            throw new \Exception("Tipe file tidak didukung: $mime");
        }

        $ext = $allowedMimes[$mime];
        $filename = Str::uuid() . '.' . $ext;

        Storage::disk('public')->put("{$folder}/{$filename}", $decoded);

        return $filename;
    }

    /**
     * Hapus file dari storage/public/{folder}
     */
    public static function deleteFile(string $folder, string $filename): bool
    {
        $path = "{$folder}/{$filename}";
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false; // file tidak ditemukan
    }

    public static function getFileUrl(string $folder, string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        return Storage::disk('public')->url("{$folder}/{$filename}");
    }
}
