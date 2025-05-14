<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentAddressRequest;
use App\Http\Requests\UpdateStudentAddressRequest;
use App\Models\StudentAddress;


class StudentAddressController extends Controller
{
    public function index($id)
    {
        $address = StudentAddress::where('student_id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($address, 'List Student Address');
    }

    public function show($studentId, $id)
    {
        $address = StudentAddress::where('id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($address, 'View Student Address');
    }

    public function store(CreateStudentAddressRequest $request, $studentId)
    {
        print ($studentId);
        $request->validated();
        $id = uuid_create();
        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['street'] = $request['street'];

        StudentAddress::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Student Address');
    }


    public function destroy($id)
    {
        $address = StudentAddress::find($id);

        if ($address) {
            $address->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Student Address'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateStudentAddressRequest $request, $studentId, $id)
    {
        $request->validated();

        $address = StudentAddress::find($id);

        if (!$address) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['street'] = $request['street'];
        $data['updated_by_id'] = $request->user()->id;
        $data['updated_by'] = now();

        $address->update($data);

        return ResponseFormatter::success([
            'id' => $address->id
        ], 'Success update Student Address');
    }

}
