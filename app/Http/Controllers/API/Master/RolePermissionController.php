<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;


class RolePermissionController extends Controller
{
    public function index()
    {
        $rolePermissions = RolePermission::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($rolePermissions, 'List Role RolePermission');
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required',
            'permission_id' => 'required'
        ]);

        $user = $request->user();
        $id = uuid_create();
        RolePermission::create([
            'id' => $id,
            'role_id' => $request->role_id,
            'permission_id' => $request->permission_id,
            'created_by_id' => $user->id,
            'created_at' => now()
        ]);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Role Permission');
    }


    public function destroy(Request $request, $id)
    {
        $roleRolePermission = RolePermission::find($id);

        if ($roleRolePermission) {
            $roleRolePermission->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Role Permission'
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

        $roleRolePermission = RolePermission::find($id);
        $user = $request->user();

        if (!$roleRolePermission) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $roleRolePermission->update([
            'name' => $request->name,
            'updated_by_id' => $user->id,
            'updated_at' => now()
        ]);

        return ResponseFormatter::success([
            'id' => $roleRolePermission->id
        ], 'Success update Role Permission');
    }

}
