<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Simpan file base64 ke R2
     *
     * @param string $base64
     * @param string $folder
     * @param int $maxSizeMb
     * @return string
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

        // Simpan ke R2
        Storage::disk('r2')->put("{$folder}/{$filename}", $decoded, 'public');


        return $filename;
    }

    /**
     * Hapus file dari R2
     */
    public static function deleteFile(string $folder, string $filename): bool
    {
        $path = "{$folder}/{$filename}";
        if (Storage::disk('r2')->exists($path)) {
            return Storage::disk('r2')->delete($path);
        }

        return false; // file tidak ditemukan
    }

    /**
     * Ambil URL file dari R2
     */
    public static function getFileUrl(string $folder, string $filename): string
    {
        if (!$filename) {
            return '';
        }

        $path = "{$folder}/{$filename}";

        if (!Storage::disk('r2')->exists($path)) {
            return '';
        }

        return Storage::disk('r2')->temporaryUrl(
            $path,
            now()->addMinutes(30)
        );
    }
}
