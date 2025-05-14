<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\MasterCache;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContactTypeRequest;
use App\Http\Requests\UpdateContactTypeRequest;
use App\Models\ContactType;
use Illuminate\Http\Request;

class ContactTypeController extends Controller
{

    public function index()
    {
        $contactType = MasterCache::getOrFetch('contact_types', 3600, function () {
            return ContactType::orderBy('created_at', 'desc')->get();
        });
        return ResponseFormatter::success($contactType, 'List Contact Type');
    }

    public function store(CreateContactTypeRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        ContactType::create($data);
        MasterCache::clear('contact_types');
        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Contact Type');
    }


    public function destroy(Request $request, $id)
    {
        $contactType = ContactType::find($id);

        if ($contactType) {
            $contactType->delete();
            MasterCache::clear('contact_types');
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Contact Type'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateContactTypeRequest $request, $id)
    {
        $data = $request->validated();
        $contactType = ContactType::find($id);

        if (!$contactType) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $contactType->update($data);
        MasterCache::clear('contact_types');
        return ResponseFormatter::success([
            'id' => $contactType->id
        ], 'Success update Contact Type');
    }

}
