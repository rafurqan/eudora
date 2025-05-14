<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentParentRequest;
use App\Http\Requests\UpdateStudentParentRequest;
use App\Models\StudentParent;


class StudentParentController extends Controller
{
    public function index($id)
    {
        $parent = StudentParent::where('student_id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($parent, 'List Student Parent');
    }

    public function show($studentId, $id)
    {
        $parent = StudentParent::where('id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($parent, 'View Student Parent');
    }

    public function store(CreateStudentParentRequest $request, $studentId)
    {
        print ($studentId);
        $request->validated();
        $id = uuid_create();
        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['education_level_id'] = $request['education_level_id'];
        $data['parent_type'] = $request['parent_type'];
        $data['full_name'] = $request['full_name'];
        $data['nik'] = $request['nik'];
        $data['occupation'] = $request['occupation'];
        $data['birth_year'] = $request['birth_year'];
        $data['income_range_id'] = $request['income_range_id'];
        $data['phone'] = $request['phone'];
        $data['is_guardian'] = $request['is_guardian'];

        StudentParent::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Student Parent');
    }


    public function destroy($id)
    {
        $parent = StudentParent::find($id);

        if ($parent) {
            $parent->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Student Parent'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateStudentParentRequest $request, $studentId, $id)
    {
        $request->validated();

        $parent = StudentParent::find($id);

        if (!$parent) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['education_level_id'] = $request['education_level_id'];
        $data['parent_type'] = $request['parent_type'];
        $data['full_name'] = $request['full_name'];
        $data['nik'] = $request['nik'];
        $data['occupation'] = $request['occupation'];
        $data['birth_year'] = $request['birth_year'];
        $data['income_range_id'] = $request['income_range_id'];
        $data['phone'] = $request['phone'];
        $data['is_guardian'] = $request['is_guardian'];
        $data['updated_by_id'] = $request->user()->id;
        $data['updated_by'] = now();

        $parent->update($data);

        return ResponseFormatter::success([
            'id' => $parent->id
        ], 'Success update Student Parent');
    }

}
