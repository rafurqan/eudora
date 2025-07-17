<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\MasterCache;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateParentTypeRequest;
use App\Http\Requests\UpdateParentTypeRequest;
use App\Models\ParentType;
use Illuminate\Http\Request;

class ParentTypeController extends Controller
{

    public function index()
    {
        $ParentType = MasterCache::getOrFetch('parent_types', 3600, function () {
            return ParentType::orderBy('code', 'asc')->get();
        });
        return ResponseFormatter::success($ParentType, 'List Parent Type');
    }

    public function store(CreateParentTypeRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;

        if (empty($data['code'])) {
            $lastCode = ParentType::select('code')
                ->whereNotNull('code')
                ->orderByRaw("LPAD(code, 10, '0') DESC")
                ->limit(1)
                ->value('code');

            $nextNumber = $lastCode ? intval($lastCode) + 1 : 1;

            $data['code'] = str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
        }

        ParentType::create($data);
        MasterCache::clear('parent_types');

        return ResponseFormatter::success([
            'id' => $id,
            'code' => $data['code']
        ], 'Success create Parent Type');
    }


    public function destroy(Request $request, $id)
    {
        $ParentType = ParentType::find($id);

        if ($ParentType) {
            $ParentType->delete();
            MasterCache::clear('parent_types');
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Parent Type'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateParentTypeRequest $request, $id)
    {
        $data = $request->validated();
        $ParentType = ParentType::find($id);

        if (!$ParentType) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $ParentType->update($data);
        MasterCache::clear('parent_types');
        return ResponseFormatter::success([
            'id' => $ParentType->id
        ], 'Success update Parent Type');
    }

}
