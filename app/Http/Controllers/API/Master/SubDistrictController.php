<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\SubDistrict;
use Illuminate\Http\Request;

class SubDistrictController extends Controller
{

    public function index(Request $request)
    {

        $subDistricts = SubDistrict::query();

        if ($request->city_id) {
            $subDistricts->where('city_id', $request->query('city_id'));
        }

        $subDistricts = $subDistricts->orderBy('name', 'asc')->get();
        return ResponseFormatter::success($subDistricts, 'List Sub District');
    }
}
