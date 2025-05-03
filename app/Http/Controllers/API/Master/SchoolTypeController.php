<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSchoolTypeRequest;
use App\Http\Requests\UpdateSchoolTypeRequest;
use App\Models\SchoolType;
use Illuminate\Http\Request;

class SchoolTypeController extends Controller
{

    public function index()
    {
        $schoolType = SchoolType::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($schoolType, 'List School Type');
    }

    public function store(CreateSchoolTypeRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        SchoolType::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create School Type');
    }


    public function destroy(Request $request, $id)
    {
        $schoolType = SchoolType::find($id);

        if ($schoolType) {
            $schoolType->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove School Type'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateSchoolTypeRequest $request, $id)
    {
        $data = $request->validated();
        $schoolType = SchoolType::find($id);

        if (!$schoolType) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $schoolType->update($data);

        return ResponseFormatter::success([
            'id' => $schoolType->id
        ], 'Success update School Type');
    }

}
