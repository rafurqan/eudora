<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Services\TeacherService;
use App\Models\Teacher;
use DB;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private TeacherService $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }
    public function index()
    {

        $teachers = $this->teacherService->index();
        return ResponseFormatter::success($teachers, 'List Teacher');
    }

    public function show($id)
    {
        $student = $this->teacherService->show($id);
        if (!$student) {
            return ResponseFormatter::error(null, 'Data Not Found', 404);
        }
        return ResponseFormatter::success($student, 'View Student');
    }

    public function store(CreateTeacherRequest $request)
    {
        $request->validated();
        try {
            $teacher = $this->teacherService->store($request->all());
            return ResponseFormatter::success([
                'id' => $teacher->id,
            ], 'Success create teacher');

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error($e, 'Failed to store teacher', 500);
        }
    }


    public function destroy(Request $request, $id)
    {
        $teacher = Teacher::find($id);

        if ($teacher) {
            $this->teacherService->delete($id);
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Teacher'
            );
        } else {
            return ResponseFormatter::errorNotFound();
        }
    }


    public function update(UpdateTeacherRequest $request, $id)
    {
        $data = $request->validated();

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $this->teacherService->update($data, $id);

        return ResponseFormatter::success([
            'id' => $teacher->id
        ], 'Success update teacher');
    }

}
