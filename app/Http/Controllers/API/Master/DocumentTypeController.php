<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDocumentTypeRequest;
use App\Http\Requests\UpdateDocumentTypeRequest;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{

    public function index()
    {
        $documentType = DocumentType::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($documentType, 'List Document Type');
    }

    public function store(CreateDocumentTypeRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        DocumentType::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Document Type');
    }


    public function destroy(Request $request, $id)
    {
        $documentType = DocumentType::find($id);

        if ($documentType) {
            $documentType->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Document Type'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateDocumentTypeRequest $request, $id)
    {
        $data = $request->validated();
        $documentType = DocumentType::find($id);

        if (!$documentType) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $documentType->update($data);

        return ResponseFormatter::success([
            'id' => $documentType->id
        ], 'Success update Document Type');
    }

}
