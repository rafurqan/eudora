<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;



class ServiceController extends Controller
{
    public function index()
    {
        $rate = Service::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($rate, 'List Layanan');
    }

    public function store(CreateServiceRequest $request)
    {
        $data = $request->validated();
        $data['created_by_id'] = $request->user()->id; // Dapatkan id user yang sedang login
        $service = Service::create($data); // UUID akan otomatis dibuat oleh model

        return ResponseFormatter::success([
            'id' => $service->id
        ], 'Success create Layanan');
    }


    public function update(UpdateServiceRequest $request, $id)
    {
        $data = $request->validated();
        $rate = Service::find($id);

        if (!$rate) {
            return ResponseFormatter::error(null, 'Data not found', 404);
        }
        $data["updated_by_id"] = $request->user()->id;

        $rate->update($data);

        return ResponseFormatter::success([
            'id' => $rate->id
        ],'Success update Layanan');
    }

    public function destroy(Request $request, $id)
    {
        $rate = Service::find($id);

        if ($rate) {
            $rate->delete();
            return ResponseFormatter::success(
                data: null,
                message: 'Success Remove Layanan'
            );
        } else {
            return ResponseFormatter::error(
                data: null,
                message: 'Data Not Found',
                code: 404
            );
        }
    }
}
