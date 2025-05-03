<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentDocumentRequest;
use App\Http\Requests\UpdateStudentDocumentRequest;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Str;


class StudentDocumentController extends Controller
{
    public function index()
    {
        $studentDocuments = StudentDocument::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($studentDocuments, 'List Student Document');
    }

    public function store(CreateStudentDocumentRequest $request)
    {
        $request->validated();

        $id = uuid_create();
        $data = $request->validated();


        try {
            $photoFilename = null;
            if (!empty($data['photo'])) {
                $photoFilename = $this->saveBase64File($data['photo'], 'documents');
            }
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            $data['file_name'] = $photoFilename;
            StudentDocument::create($data);
            return ResponseFormatter::success([
                'id' => $id
            ], 'Success create Student Document');

        } catch (\Exception $e) {
            \Log::error('Error creating student document: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Failed to create student document', 500);
        }



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


    public function update(UpdateStudentDocumentRequest $request, $id)
    {
        $request->validated();

        $studentDocuments = StudentDocument::find($id);

        if (!$studentDocuments) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }

        $data = $request->validated();
        try {
            $photoFilename = null;
            if (!empty($data['photo'])) {
                $photoFilename = $this->saveBase64File($data['photo'], 'documents');
            }
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            $data['file_name'] = $photoFilename;
            $studentDocuments->update($data);

            return ResponseFormatter::success([
                'id' => $studentDocuments->id
            ], 'Success update Student Document');

        } catch (\Exception $e) {
            \Log::error('Error creating student document: ' . $e->getMessage());
            return ResponseFormatter::error(null, 'Failed to create student document', 500);
        }


    }

    protected function saveBase64File(string $base64, string $folder): string
    {
        $imageData = explode(',', $base64)[1] ?? null;
        if (!$imageData) {
            throw new \Exception("Invalid base64 format");
        }

        $decoded = base64_decode($imageData);
        $mime = finfo_buffer(finfo_open(), $decoded, FILEINFO_MIME_TYPE);

        $ext = match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf',
            default => 'bin',
        };

        $filename = Str::uuid() . '.' . $ext;
        Storage::disk('public')->put("{$folder}/{$filename}", $decoded);

        return $filename;
    }

}
