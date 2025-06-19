<?php

namespace App\Http\Controllers\API\Master;


use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDonationTypeRequest;
use App\Http\Requests\UpdateDonationTypeRequest;
use App\Models\DonationType;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonationsTypesController extends Controller
{
    public function index()
    {
        $donor = DonationType::orderBy('created_at', 'desc')->get();
        if ($donor->count() == 0) {
            return ResponseFormatter::error(null, 'Data Tipe Donasi Kosong', 404);
        }

        return ResponseFormatter::success($donor, 'List Tipe Donasi');
    }

    public function store(CreateDonationTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $id = uuid_create();
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            $data['updated_at'] = null;
            $donor = DonationType::create($data);
            DB::commit();
            return ResponseFormatter::success([
                'id' => $id,
                'data' => $donor
            ], 'Berhasil membuat Tipe Donasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function update (UpdateDonationTypeRequest $request, $id) {
        DB::beginTransaction();
        try {
            $donor = DonationType::find($id);
            if (!$donor) {
                return ResponseFormatter::error(null, 'Data Tipe Donasi Tidak ditemukan', 404);
            }
            $data = $request->validated();
            $data["updated_by_id"] = $request->user()->id;
            $updated = $donor->update($data);
            if (!$updated) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal mengupdate data Tipe Donasi', 500);
            }
            DB::commit();
            return ResponseFormatter::success([
                'id' => $donor->id,
                'data' => $donor
            ],'Berhasil mengupdate Tipe Donasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function destroy (Request $request, $id) {
        DB::beginTransaction();
        try {
            $donor = DonationType::find($id);
            if (!$donor) {
                return ResponseFormatter::error(null, 'Data Tipe Donasi Tidak ditemukan', 404);
            }
            $deleted = $donor->delete();
            if (!$deleted) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menghapus data Tipe Donasi', 500);
            }
            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Tipe Donasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
