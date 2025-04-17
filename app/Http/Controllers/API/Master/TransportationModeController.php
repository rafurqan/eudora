<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTransportationModeRequest;
use App\Http\Requests\UpdateTransportationModeRequest;
use App\Models\TransportationMode;
use Illuminate\Http\Request;

class TransportationModeController extends Controller
{

    public function all()
    {
        $transportationMode = TransportationMode::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($transportationMode, 'List Transportation Mode');
    }

    public function create(CreateTransportationModeRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        TransportationMode::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Transportation Mode');
    }


    public function destroy(Request $request, $id)
    {
        $transportationMode = TransportationMode::find($id);

        if ($transportationMode) {
            $transportationMode->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Transportation Mode'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateTransportationModeRequest $request, $id)
    {
        $data = $request->validated();
        $transportationMode = TransportationMode::find($id);

        if (!$transportationMode) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $transportationMode->update($data);

        return ResponseFormatter::success([
            'id' => $transportationMode->id
        ], 'Success update Transportation Mode');
    }

}
