<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRateRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Models\Rate;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;

class RateController extends Controller
{
    public function index()
    {
        $rate = Rate::orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success($rate, 'List Tarif');
    }

    public function store(CreateRateRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            $id = uuid_create();
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            
            $rate = Rate::create($data);
            
            if (!$rate) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menyimpan data tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success([
                'id' => $id,
                'data' => $rate
            ], 'Berhasil membuat Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateRateRequest $request, $id)
    {   
        try {
            DB::beginTransaction();
            
            $rate = Rate::find($id);
            if (!$rate) {
                return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
            }

            $data = $request->validated();
            $data["updated_by_id"] = $request->user()->id;
            
            $updated = $rate->update($data);
            if (!$updated) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal mengupdate data tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success([
                'id' => $rate->id,
                'data' => $rate
            ],'Berhasil mengupdate Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $rate = Rate::find($id);
            if (!$rate) {
                return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
            }

            $deleted = $rate->delete();
            if (!$deleted) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menghapus data tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
