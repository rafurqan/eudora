<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentClassRequest;
use App\Http\Requests\UpdateStudentClassRequest;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentClassController extends Controller
{
    public function index()
    {
        $studentClass = StudentClass::with(['teacher', 'program'])->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($studentClass, 'List Student Class');
    }


    public function store(CreateStudentClassRequest $request)
    {

        $data = $request->validated();

        $user = $request->user();
        $id = uuid_create();
        StudentClass::create([
            'id' => $id,
            'name' => $data['name'] ?? null,
            'part' => $data['part'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'academic_year' => $data['academic_year'] ?? null,
            'program_id' => $data['program']['id'] ?? null,
            'teacher_id' => $data['teacher']['id'] ?? null,
            'status' => $data['status'] ?? null,
            'created_by_id' => $user->id,
        ]);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Student Class');
    }


    public function destroy(Request $request, $id)
    {
        $studentClass = StudentClass::find($id);

        if ($studentClass) {
            $studentClass->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Student Class'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateStudentClassRequest $request, $id)
    {
        $data = $request->validated();

        $user = $request->user();
        $studentClass = StudentClass::find($id);

        if (!$studentClass) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $studentClass->update([
            'id' => $id,
            'name' => $data['name'] ?? null,
            'part' => $data['part'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'academic_year' => $data['academic_year'] ?? null,
            'program_id' => $data['program']['id'] ?? null,
            'teacher_id' => $data['teacher']['id'] ?? null,
            'status' => $data['status'] ?? null,
            'updated_by_id' => $user->id,
        ]);

        return ResponseFormatter::success([
            'id' => $studentClass->id
        ], 'Success update Student Class');
    }

}
