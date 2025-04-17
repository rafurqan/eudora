<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($teachers, 'List Teacher');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $user = $request->user();
        $id = uuid_create();
        Teacher::create([
            'id' => $id,
            'name' => $request->name,
        ]);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create teacher');
    }


    public function destroy(Request $request, $id)
    {
        $teacher = Teacher::find($id);

        if ($teacher) {
            $teacher->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Teacher'
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

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $teacher->update([
            'name' => $request->name,
        ]);

        return ResponseFormatter::success([
            'id' => $teacher->id
        ], 'Success update teacher');
    }

}
