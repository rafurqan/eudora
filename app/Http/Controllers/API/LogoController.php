<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;

class LogoController extends Controller
{
    public function show()
    {
        $path = storage_path('app/public/school-logo.png');

        if (!file_exists($path)) {
            return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
        }

        return ResponseFormatter::success($path, 'Logo Found Ditemukan');
    }
}
