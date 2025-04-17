<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSpecialNeedRequest;
use App\Http\Requests\UpdateSpecialNeedRequest;
use App\Models\SpecialNeed;
use Illuminate\Http\Request;

class SpecialNeedController extends Controller
{

    public function index()
    {
        $specialNeed = SpecialNeed::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($specialNeed, 'List Special Need');
    }

    public function store(CreateSpecialNeedRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        SpecialNeed::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Special Need');
    }


    public function destroy(Request $request, $id)
    {
        $specialNeed = SpecialNeed::find($id);

        if ($specialNeed) {
            $specialNeed->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Special Need'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateSpecialNeedRequest $request, $id)
    {
        $data = $request->validated();
        $specialNeed = SpecialNeed::find($id);

        if (!$specialNeed) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $specialNeed->update($data);

        return ResponseFormatter::success([
            'id' => $specialNeed->id
        ], 'Success update Special Need');
    }

}
