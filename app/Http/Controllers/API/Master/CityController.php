<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{

    public function index(Request $request)
    {

        $cities = City::query();

        if ($request->province_id) {
            $cities->where('province_id', $request->query());
        }

        $cities = $cities->orderBy('name', 'asc')->get();
        return ResponseFormatter::success($cities, 'List City');
    }
}
