<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;


class PermissionController extends Controller
{
    public function all()
    {
        $permissions = Permission::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($permissions, 'List Permission');
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $user = $request->user();
        $id = uuid_create();
        Permission::create([
            'id' => $id,
            'name' => $request->name,
            'created_by_id' => $user->id,
            'created_at' => now(),
        ]);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Permission');
    }


    public function destroy(Request $request, $id)
    {
        $permission = Permission::find($id);

        if ($permission) {
            $permission->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Permission'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $permission = Permission::find($id);
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
        ], 'Success update Permission');
    }

}
