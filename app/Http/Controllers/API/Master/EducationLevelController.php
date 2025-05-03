<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\MasterCache;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEducationLevelRequest;
use App\Http\Requests\UpdateEducationLevelRequest;
use App\Models\EducationLevel;
use Illuminate\Http\Request;

class EducationLevelController extends Controller
{

    public function index()
    {
        $educationLevel = MasterCache::getOrFetch('education_levels', 3660, function () {
            return EducationLevel::orderBy('created_at', 'desc')->get();
        });
        return ResponseFormatter::success($educationLevel, 'List Education Level');
    }

    public function store(CreateEducationLevelRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        EducationLevel::create($data);
        MasterCache::clear('education_levels');
        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create education level');
    }


    public function destroy(Request $request, $id)
    {
        $education = EducationLevel::find($id);

        if ($education) {
            $education->delete();
            MasterCache::clear('education_levels');
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Education Level'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateEducationLevelRequest $request, $id)
    {
        $data = $request->validated();
        $education = EducationLevel::find($id);

        if (!$education) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $education->update($data);
        MasterCache::clear('education_levels');

        return ResponseFormatter::success([
            'id' => $education->id
        ], 'Success update education level');
    }

}
