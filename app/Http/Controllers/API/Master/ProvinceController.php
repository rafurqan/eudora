<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{

    public function index()
    {
        $province = Province::orderBy('name', 'asc')->get();
        return ResponseFormatter::success($province, 'List Province');
    }
}
