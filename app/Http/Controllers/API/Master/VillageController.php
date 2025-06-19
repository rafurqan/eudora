<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Village;
use Illuminate\Http\Request;

class VillageController extends Controller
{

    public function index(Request $request)
    {

        $villages = Village::query();

        if ($request->sub_district_id) {
            $villages->where('sub_district_id', $request->query('sub_district_id'));
        }

        $villages = $villages->orderBy('name', 'asc')->get();
        return ResponseFormatter::success($villages, 'List Village');
    }
}
