<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNationalityRequest;
use App\Http\Requests\UpdateNationalityRequest;
use App\Models\Nationality;
use Illuminate\Http\Request;

class NationalityController extends Controller
{

    public function all()
    {
        $nationality = Nationality::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($nationality, 'List Nationality');
    }

    public function create(CreateNationalityRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        Nationality::create($data);

        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Document Type');
    }


    public function destroy(Request $request, $id)
    {
        $nationality = Nationality::find($id);

        if ($nationality) {
            $nationality->delete();
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


    public function update(UpdateNationalityRequest $request, $id)
    {
        $data = $request->validated();
        $nationality = Nationality::find($id);

        if (!$nationality) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $nationality->update($data);

        return ResponseFormatter::success([
            'id' => $nationality->id
        ], 'Success update Nationality');
    }

}
