<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\MasterCache;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{

    public function index()
    {
        $programs = MasterCache::getOrFetch('programs', 3660, function () {
            return Program::orderBy('created_at', 'desc')->get();
        });
        return ResponseFormatter::success($programs, 'List Program');
    }

    public function store(CreateProgramRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        Program::create($data);
        MasterCache::clear('programs');
        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create program');
    }


    public function destroy(Request $request, $id)
    {
        $program = Program::find($id);

        if ($program) {
            $program->delete();
            MasterCache::clear('programs');
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Program'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateProgramRequest $request, $id)
    {
        $data = $request->validated();
        $program = Program::find($id);

        if (!$program) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $program->update($data);
        MasterCache::clear('programs');

        return ResponseFormatter::success([
            'id' => $program->id
        ], 'Success update program');
    }

}
