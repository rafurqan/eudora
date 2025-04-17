<?php

namespace App\Http\Controllers\API\Log;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{

    public function all()
    {
        $logPath = storage_path('logs/activity.log');

        if (!File::exists($logPath)) {
            return response()->json(['message' => 'Log file not found'], 404);
        }

        $lines = File::lines($logPath)->toArray();

        $logs = collect($lines)->map(function ($line) {
            // Cari posisi JSON, setelah karakter `}` pertama
            $jsonStart = strpos($line, '{');
            if ($jsonStart !== false) {
                $jsonPart = substr($line, $jsonStart);
                $parsed = json_decode($jsonPart, true);

                return $parsed ? $parsed : ['raw' => $line];
            }

            return ['raw' => $line];
        })->reverse()->values(); // reverse biar yang terbaru di atas

        return ResponseFormatter::success($logs, 'List Log');
    }

}
