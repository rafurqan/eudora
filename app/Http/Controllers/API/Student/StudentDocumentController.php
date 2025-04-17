<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentDocumentRequest;
use App\Models\StudentDocument;
use Illuminate\Http\Request;


class StudentDocumentController extends Controller
{
    public function all()
    {
        $studentDocuments = StudentDocument::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($studentDocuments, 'List Student Document');
    }

    public function create(CreateStudentDocumentRequest $request)
    {
        $request->validated();

        $id = uuid_create();
        $data = $request->validated();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        $data['file_name'] = '';

        StudentDocument::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Student Document');
    }


    public function destroy(Request $request, $id)
    {
        $studentDocuments = StudentDocument::find($id);

        if ($studentDocuments) {
            $studentDocuments->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Student Document'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(CreateStudentDocumentRequest $request, $id)
    {
        $request->validated();

        $studentDocuments = StudentDocument::find($id);

        if (!$studentDocuments) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $id = uuid_create();
        $data = $request->validated();
        $data['id'] = $id;
        $data['updated_by_id'] = $request->user()->id;
        $data['file_name'] = '';

        $studentDocuments->update($data);

        return ResponseFormatter::success([
            'id' => $studentDocuments->id
        ], 'Success update Student Document');
    }

}
