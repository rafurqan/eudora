<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\MasterCache;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateIncomeRangeRequest;
use App\Http\Requests\UpdateIncomeRangeRequest;
use App\Models\IncomeRange;
use Illuminate\Http\Request;

class IncomeRangeController extends Controller
{

    public function index()
    {
        $incomeRange = MasterCache::getOrFetch('income_ranges', 3600, function () {
            return IncomeRange::orderBy('created_at', 'desc')->get();
        });
        return ResponseFormatter::success($incomeRange, 'List Income Range');
    }

    public function store(CreateIncomeRangeRequest $request)
    {
        $data = $request->validated();
        $id = uuid_create();
        $data['id'] = $id;
        $data['created_by_id'] = $request->user()->id;
        IncomeRange::create($data);
        MasterCache::clear('income_ranges');
        return ResponseFormatter::success([
            'id' => $id
        ], 'Success create Income Range');
    }


    public function destroy(Request $request, $id)
    {
        $incomeRange = IncomeRange::find($id);

        if ($incomeRange) {
            $incomeRange->delete();
            MasterCache::clear('income_ranges');
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Income Range'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }


    public function update(UpdateIncomeRangeRequest $request, $id)
    {
        $data = $request->validated();
        $incomeRange = IncomeRange::find($id);

        if (!$incomeRange) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $incomeRange->update($data);
        MasterCache::clear('income_ranges');
        return ResponseFormatter::success([
            'id' => $incomeRange->id
        ], 'Success update Income Range');
    }

}
