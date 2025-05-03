<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentContactRequest;
use App\Http\Requests\UpdateStudentContactRequest;
use App\Models\StudentContact;


class StudentContactController extends Controller
{
    public function index($id)
    {
        $contact = StudentContact::where('student_id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($contact, 'List Student Contact');
    }

    public function show($studentId, $id)
    {
        $contact = StudentContact::where('id', $id)->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($contact, 'View Student Contact');
    }

    public function store(CreateStudentContactRequest $request, $studentId)
    {
        print ($studentId);
        $id = uuid_create();
        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['value'] = $request['value'];
        $data['contact_type_id'] = $request['contact_type_id'];


        StudentContact::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Student Contact');
    }


    public function destroy($id)
    {
        $contact = StudentContact::find($id);

        if ($contact) {
            $contact->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Student Contact'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateStudentContactRequest $request, $studentId, $id)
    {
        $request->validated();

        $contact = StudentContact::find($id);

        if (!$contact) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $data = $request->validated();
        $data['id'] = $id;
        $data['student_id'] = $studentId;
        $data['created_by_id'] = $request->user()->id;
        $data['value'] = $request['value'];
        $data['contact_type_id'] = $request['contact_type_id'];
        $data['updated_by_id'] = $request->user()->id;
        $data['updated_by'] = now();

        $contact->update($data);

        return ResponseFormatter::success([
            'id' => $contact->id
        ], 'Success update Student Contact');
    }

}
