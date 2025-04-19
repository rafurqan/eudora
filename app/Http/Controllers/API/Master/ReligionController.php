<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReligionRequest;
use App\Http\Requests\UpdateReligionRequest;
use App\Models\Religion;
use Illuminate\Http\Request;


class ReligionController extends Controller
{
    public function index()
    {
        $permissions = Religion::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($permissions, 'List Religion');
    }

    public function store(CreateReligionRequest $request)
    {
        $request->validated();

        $user = $request->user();
        $id = uuid_create();
        Religion::create([
            'id' => $id,
            'name' => $request->name,
            'created_by_id' => $user->id,
            'created_at' => now(),
        ]);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Religion');
    }


    public function destroy(Request $request, $id)
    {
        $permission = Religion::find($id);

        if ($permission) {
            $permission->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Religion'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateReligionRequest $request, $id)
    {
        $request->validated();

        $permission = Religion::find($id);
        $user = $request->user();

        if (!$permission) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $permission->update([
            'name' => $request->name,
            'updated_by_id' => $user->id,
            'updated_at' => now()
        ]);

        return ResponseFormatter::success([
            'id' => $permission->id
        ], 'Success update Religion');
    }

}
