<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRatePackageRequest;
use App\Http\Requests\UpdateRatePackageRequest;
use App\Models\RatePackage;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;

class RatePackageController extends Controller
{
    public function index()
    {
        $rate = RatePackage::join('services', 'rates.service_id', '=', 'services.id')
        ->whereNotNull('rates.child_ids')
        ->whereJsonLength('rates.child_ids', '>', 0)
        ->where(function ($query) {
            $query->whereJsonDoesntContain('rates.child_ids', ['']);
        })
        ->orderBy('rates.created_at', 'desc')
        ->select('rates.*', 'services.name as nama_tarif')
        ->get();
        // dd($rate);
        return ResponseFormatter::success($rate, 'List Paket Tarif');
    }

    public function store(CreateRatePackageRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            // dd($data);
            $id = uuid_create();
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            
            $rate = RatePackage::create($data);
            
            if (!$rate) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menyimpan data paket tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success([
                'id' => $id,
                'data' => $rate
            ], 'Berhasil membuat Paket Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateRatePackageRequest $request, $id)
    {   
        try {
            DB::beginTransaction();
            
            $rate = RatePackage::find($id);
            if (!$rate) {
                return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
            }

            $data = $request->validated();
            $data["updated_by_id"] = $request->user()->id;
            
            $updated = $rate->update($data);
            if (!$updated) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal mengupdate data paket tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success([
                'id' => $rate->id,
                'data' => $rate
            ],'Berhasil mengupdate Paket Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $rate = RatePackage::find($id);
            if (!$rate) {
                return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
            }

            $deleted = $rate->delete();
            if (!$deleted) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menghapus data paket tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Paket Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
