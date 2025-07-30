<?php

// namespace App\Http\Controllers\API\Log;

// use App\Helpers\ResponseFormatter;
// use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\File;

// class LogController extends Controller
// {

//     public function index()
//     {
//         $logPath = storage_path('logs/activity.log');

//         if (!File::exists($logPath)) {
//             return response()->json(['message' => 'Log file not found'], 404);
//         }

//         $lines = File::lines($logPath)->toArray();

//         $logs = collect($lines)->map(function ($line) {
//             // Cari posisi JSON, setelah karakter `}` pertama
//             $jsonStart = strpos($line, '{');
//             if ($jsonStart !== false) {
//                 $jsonPart = substr($line, $jsonStart);
//                 $parsed = json_decode($jsonPart, true);

//                 return $parsed ? $parsed : ['raw' => $line];
//             }

//             return ['raw' => $line];
//         })->reverse()->values(); // reverse biar yang terbaru di atas

//         return ResponseFormatter::success($logs, 'List Log');
//     }

// }


namespace App\Http\Controllers\API\Log;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{

    public function index(Request $request)
    {
        $logPath = storage_path('logs/activity.log');

        if (!File::exists($logPath)) {
            return ResponseFormatter::error(null, 'Log file not found', 404);
        }

        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 20);

        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage)); // Limit max per_page to 100

        $lines = File::lines($logPath)->toArray();

        $allLogs = collect($lines)->map(function ($line) {
            $jsonStart = strpos($line, '{');
            if ($jsonStart !== false) {
                $jsonPart = substr($line, $jsonStart);
                $parsed = json_decode($jsonPart, true);

                return $parsed ? $parsed : ['raw' => $line];
            }

            return ['raw' => $line];
        });

        $sortedLogs = $allLogs->sortByDesc(function ($log) {
            if (isset($log['timestamp'])) {
                return $log['timestamp'];
            }
            return 0;
        })->values();

        $total = $sortedLogs->count();
        $offset = ($page - 1) * $perPage;
        $paginatedLogs = $sortedLogs->slice($offset, $perPage)->values();

        return ResponseFormatter::success(
            $paginatedLogs,
            'List Log',
            total: $total,
            page: $page,
            perPage: $perPage
        );
    }

}
