<?php
namespace App\Scheduled;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class ClearOldLog
{
    public function __invoke()
    {
        $logPath = storage_path('logs/activity.log');

        if (!File::exists($logPath)) {
            // Jika file log tidak ada, keluar
            return;
        }

        // Ambil semua baris dalam file log
        $lines = File::lines($logPath)->toArray();

        // Filter baris berdasarkan tanggal yang lebih baru dari 30 hari
        $filtered = collect($lines)->filter(function ($line) {
            // Cari posisi JSON pertama di dalam baris
            $jsonStart = strpos($line, '{');
            if ($jsonStart === false) {
                return false; // Jika tidak ada JSON, lewati baris ini
            }

            // Ambil bagian JSON dari baris
            $jsonPart = substr($line, $jsonStart);
            $parsed = json_decode($jsonPart, true);

            if (!$parsed || !isset($parsed['date'])) {
                return false; // Jika tidak ada tanggal dalam log, lewati baris
            }

            // Parse tanggal dan bandingkan dengan waktu sekarang
            $logDate = Carbon::parse($parsed['date']);
            return $logDate->gt(now()->subDays(30)); // Hanya ambil log lebih baru dari 30 hari
        });

        // Jika ada log yang tersisa, tuliskan kembali ke file
        if ($filtered->isNotEmpty()) {
            File::put($logPath, $filtered->implode(PHP_EOL) . PHP_EOL);
        } else {
            // Jika tidak ada log yang tersisa, kosongkan file
            File::put($logPath, '');
        }
    }
}
