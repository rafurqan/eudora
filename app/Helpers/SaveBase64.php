<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Str;

class SaveBase64
{
    public static function saveBase64File(string $base64, string $folder): string
    {
        $imageData = explode(',', $base64)[1] ?? null;
        if (!$imageData) {
            throw new \Exception("Invalid base64 format");
        }

        $decoded = base64_decode($imageData);
        $mime = finfo_buffer(finfo_open(), $decoded, FILEINFO_MIME_TYPE);

        $ext = match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf',
            default => 'bin',
        };

        $filename = Str::uuid() . '.' . $ext;
        Storage::disk('public')->put("{$folder}/{$filename}", $decoded);

        return $filename;
    }
}


