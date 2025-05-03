<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with(['educationLevel'])->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($teachers, 'List Teacher');
    }

    public function store(CreateTeacherRequest $request)
    {
        $request->validated();

        $id = uuid_create();
        $data['id'] = $id;
        $data['name'] = $request->name;
        $data['nip'] = $request->nip;
        $data['birth_place'] = $request->birth_place;
        $data['birth_date'] = $request->birth_date;
        $data['graduated_from'] = $request->graduated_from;
        $data['education_level_id'] = $request->education_level['id'] ?? null;
        $data['created_at'] = now();
        $data['created_by_id'] = $request->user()->id;
        Teacher::create($data);

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


    public function update(UpdateTeacherRequest $request, $id)
    {
        $data = $request->validated();

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;
        $teacher->update($data);

        return ResponseFormatter::success([
            'id' => $teacher->id
        ], 'Success update teacher');
    }

}
