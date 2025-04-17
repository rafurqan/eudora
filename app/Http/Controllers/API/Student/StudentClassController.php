<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentClassController extends Controller
{
    public function index()
    {
        $studentClass = StudentClass::with(['teacher', 'education'])->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($studentClass, 'List Student Class');
    }


    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'part' => 'required',
            'capacity' => 'required',
            'academic_year' => 'required',
            'teacher_id' => 'required',
            'education_level_id' => 'required|uuid',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $user = $request->user();
        $id = uuid_create();
        \Log::info('Education Level:', ['education_level_id' => $request->education_level_id]);
        StudentClass::create([
            'id' => $id,
            'name' => $request->name,
            'part' => $request->part,
            'capacity' => $request->capacity,
            'academic_year' => $request->academic_year,
            'education_level_id' => $request->education_level_id,
            'teacher_id' => $request->teacher_id,
            'status' => $request->status,
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


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'part' => 'required',
            'capacity' => 'required',
            'academic_year' => 'required',
            'teacher_id' => 'required',
            'education_level_id' => 'required|uuid',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $user = $request->user();
        $studentClass = StudentClass::find($id);

        if (!$studentClass) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $studentClass->update([
            'id' => $id,
            'name' => $request->name,
            'part' => $request->part,
            'capacity' => $request->capacity,
            'academic_year' => $request->academic_year,
            'teacher_id' => $request->teacher_id,
            'status' => $request->status,
            'updated_by_id' => $user->id,
        ]);

        return ResponseFormatter::success([
            'id' => $studentClass->id
        ], 'Success update Student Class');
    }

}
