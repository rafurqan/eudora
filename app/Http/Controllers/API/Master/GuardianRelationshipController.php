<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGuardianRelationshipRequest;
use App\Http\Requests\UpdateGuardianRelationshipRequest;
use App\Models\GuardianRelationships;
use Illuminate\Http\Request;

class GuardianRelationshipController extends Controller
{

    public function all()
    {
        $guardianRelationships = GuardianRelationships::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($guardianRelationships, 'List Guardian Relationship');
    }

    public function create(CreateGuardianRelationshipRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        GuardianRelationships::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Guardian Relationship');
    }


    public function destroy(Request $request, $id)
    {
        $guardianRelationship = GuardianRelationships::find($id);

        if ($guardianRelationship) {
            $guardianRelationship->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Guardian Relationship'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateGuardianRelationshipRequest $request, $id)
    {
        $data = $request->validated();
        $guardianRelationship = GuardianRelationships::find($id);

        if (!$guardianRelationship) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $guardianRelationship->update($data);

        return ResponseFormatter::success([
            'id' => $guardianRelationship->id
        ], 'Success update Guardian Relationship');
    }

}
