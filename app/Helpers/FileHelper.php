<?php

// namespace App\Helpers;

// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Str;

// class FileHelper
// {
//     /**
//      * Simpan file base64 ke storage/public/{folder}
//      *
//      * @throws \Exception
//      */
//     public static function saveBase64File(string $base64, string $folder, int $maxSizeMb = 2): string
//     {
//         $imageData = explode(',', $base64)[1] ?? null;
//         if (!$imageData) {
//             throw new \Exception("Format base64 tidak valid");
//         }

//         $decoded = base64_decode($imageData);
//         if ($decoded === false) {
//             throw new \Exception("Base64 decode gagal");
//         }

//         // Validasi ukuran maksimal
//         $maxSize = $maxSizeMb * 1024 * 1024; // dalam byte
//         if (strlen($decoded) > $maxSize) {
//             throw new \Exception("Ukuran file melebihi {$maxSizeMb}MB");
//         }

//         // Deteksi MIME
//         $finfo = finfo_open();
//         $mime = finfo_buffer($finfo, $decoded, FILEINFO_MIME_TYPE);

//         // Validasi tipe yang diizinkan
//         $allowedMimes = [
//             'image/png' => 'png',
//             'image/jpeg' => 'jpg',
//             'application/pdf' => 'pdf',
//         ];

//         if (!isset($allowedMimes[$mime])) {
//             throw new \Exception("Tipe file tidak didukung: $mime");
//         }

//         $ext = $allowedMimes[$mime];
//         $filename = Str::uuid() . '.' . $ext;

//         Storage::disk('public')->put("{$folder}/{$filename}", $decoded);

//         return $filename;
//     }

//     /**
//      * Hapus file dari storage/public/{folder}
//      */
//     public static function deleteFile(string $folder, string $filename): bool
//     {
//         $path = "{$folder}/{$filename}";
//         if (Storage::disk('public')->exists($path)) {
//             return Storage::disk('public')->delete($path);
//         }

//         return false; // file tidak ditemukan
//     }
// }


namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

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

        $maxSize = $maxSizeMb * 1024 * 1024; // dalam byte
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
        $filename = Str::uuid() . '.' . $ext;

        if (app()->environment('production')) {
            $publicPath = public_path($folder);

            if (!File::exists($publicPath)) {
                File::makeDirectory($publicPath, 0755, true);
            }
            file_put_contents($publicPath . '/' . $filename, $decoded);
        } else {
            Storage::disk('public')->put("{$folder}/{$filename}", $decoded);
        }

        return $filename;
    }


    public static function deleteFile(string $folder, string $filename): bool
    {
        if (app()->environment('production')) {
            $publicPath = public_path($folder . '/' . $filename);
            if (File::exists($publicPath)) {
                return File::delete($publicPath);
            }
        } else {
            $path = "{$folder}/{$filename}";
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
        }

        return false;
    }

    public static function getFileUrl(string $folder, string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        if (app()->environment('production')) {
            return config('app.url') . '/' . $folder . '/' . $filename;
        } else {
            return Storage::disk('public')->url($folder . '/' . $filename);
        }
    }

    public static function fileExists(string $folder, string $filename): bool
    {
        if (app()->environment('production')) {
            return File::exists(public_path($folder . '/' . $filename));
        } else {
            return Storage::disk('public')->exists($folder . '/' . $filename);
        }
    }
}
