<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\MasterCache;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Models\Education;
use Illuminate\Http\Request;

class EducationController extends Controller
{

    public function index()
    {
        $Educations = MasterCache::getOrFetch('Educations', 3660, function () {
            return Education::orderBy('created_at', 'desc')->get();
        });
        return ResponseFormatter::success($Educations, 'List Education');
    }

    public function store(CreateEducationRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        Education::create($data);
        MasterCache::clear('Educations');
        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Education');
    }


    public function destroy(Request $request, $id)
    {
        $Education = Education::find($id);

        if ($Education) {
            $Education->delete();
            MasterCache::clear('Educations');
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Education'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateEducationRequest $request, $id)
    {
        $data = $request->validated();
        $Education = Education::find($id);

        if (!$Education) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $Education->update($data);
        MasterCache::clear('Educations');

        return ResponseFormatter::success([
            'id' => $Education->id
        ], 'Success update Education');
    }

}
