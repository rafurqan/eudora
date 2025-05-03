<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSpecialConditionRequest;
use App\Http\Requests\UpdateSpecialConditionRequest;
use App\Models\SpecialCondition;
use Illuminate\Http\Request;

class SpecialConditionController extends Controller
{

    public function index()
    {
        $specialCondition = SpecialCondition::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($specialCondition, 'List Special Condition');
    }

    public function store(CreateSpecialConditionRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        SpecialCondition::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Special Condition');
    }


    public function destroy(Request $request, $id)
    {
        $specialCondition = SpecialCondition::find($id);

        if ($specialCondition) {
            $specialCondition->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Special Condition'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateSpecialConditionRequest $request, $id)
    {
        $data = $request->validated();
        $specialCondition = SpecialCondition::find($id);

        if (!$specialCondition) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $specialCondition->update($data);

        return ResponseFormatter::success([
            'id' => $specialCondition->id
        ], 'Success update Special Condition');
    }

}
