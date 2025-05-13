<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRateRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Models\Rate;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;



class RateController extends Controller
{
    public function index()
    {
        $rate = Rate::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($rate, 'List Tarif');
    }

    public function store(CreateRateRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        Rate::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Tarif');
    }

    public function update(UpdateRateRequest $request, $id)
    {
        $data = $request->validated();
        $rate = Rate::find($id);

        if (!$rate) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $rate->update($data);

        return ResponseFormatter::success([
            'id' => $rate->id
        ],'Success update Tarif');
    }

    public function destroy(Request $request, $id)
    {
        $rate = Rate::find($id);

        if ($rate) {
            $rate->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Tarif'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }
}
