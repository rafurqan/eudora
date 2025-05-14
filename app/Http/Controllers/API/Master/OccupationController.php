<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\MasterCache;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOccupationRequest;
use App\Http\Requests\UpdateOccupationRequest;
use App\Models\Occupation;
use Illuminate\Http\Request;

class OccupationController extends Controller
{

    public function index()
    {
        $occupation = MasterCache::getOrFetch('occupations', 3600, function () {
            return Occupation::orderBy('created_at', 'desc')->get();
        });
        return ResponseFormatter::success($occupation, 'List Occupation');
    }

    public function store(CreateOccupationRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        Occupation::create($data);
        MasterCache::clear('occupations');
        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Occupation');
    }


    public function destroy(Request $request, $id)
    {
        $occupation = Occupation::find($id);

        if ($occupation) {
            $occupation->delete();
            MasterCache::clear('occupations');
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Occupation'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateOccupationRequest $request, $id)
    {
        $data = $request->validated();
        $occupation = Occupation::find($id);

        if (!$occupation) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $occupation->update($data);
        MasterCache::clear('occupations');
        return ResponseFormatter::success([
            'id' => $occupation->id
        ], 'Success update Occupation');
    }

}
