<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentOriginSchoolRequest;
use App\Http\Requests\UpdateStudentOriginSchoolRequest;
use App\Models\StudentOriginSchool;


class StudentOriginSchoolController extends Controller
{
    public function index($id)
    {
        $originSchool = StudentOriginSchool::where('student_id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($originSchool, 'List Student Origin School');
    }

    public function show($studentId, $id)
    {
        $originSchool = StudentOriginSchool::where('id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($originSchool, 'View Student Origin School');
    }

    public function store(CreateStudentOriginSchoolRequest $request, $studentId)
    {
        print ($studentId);
        $request->validated();
        $id = uuid_create();
        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['education_level_id'] = $request['education_level_id'];
        $data['school_type_id'] = $request['school_type_id'];
        $data['school_name'] = $request['school_name'];
        $data['npsn'] = $request['npsn'];
        $data['address_name'] = $request['address_name'];

        StudentOriginSchool::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Student Origin School');
    }


    public function destroy($id)
    {
        $originSchool = StudentOriginSchool::find($id);

        if ($originSchool) {
            $originSchool->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Student Origin School'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateStudentOriginSchoolRequest $request, $studentId, $id)
    {
        $request->validated();

        $originSchool = StudentOriginSchool::find($id);

        if (!$originSchool) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['education_level_id'] = $request['education_level_id'];
        $data['school_type_id'] = $request['school_type_id'];
        $data['school_name'] = $request['school_name'];
        $data['npsn'] = $request['npsn'];
        $data['address_name'] = $request['address_name'];
        $data['updated_by_id'] = $request->user()->id;
        $data['updated_by'] = now();

        $originSchool->update($data);

        return ResponseFormatter::success([
            'id' => $originSchool->id
        ], 'Success update Student Origin School');
    }

}
