<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDonorRequest;
use App\Http\Requests\UpdateDonorRequest;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DonorsController extends Controller
{
    public function index()
    {
        $donor = Donor::orderBy('created_at', 'desc')->get();
        if ($donor->count() == 0) {
            return ResponseFormatter::error(null, 'Data Donatur Kosong', 404);
        }

        return ResponseFormatter::success($donor, 'List Donatur');
    }

    public function store(CreateDonorRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $id = uuid_create();
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            $data['updated_at'] = null;
            $donor = Donor::create($data);
            DB::commit();
            return ResponseFormatter::success([
                'id' => $id,
                'data' => $donor
            ], 'Berhasil membuat Donatur');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function update (updateDonorRequest $request, $id) {
        DB::beginTransaction();
        try {
            $donor = Donor::find($id);
            if (!$donor) {
                return ResponseFormatter::error(null, 'Data Donatur Tidak ditemukan', 404);
            }
            $data = $request->validated();
            $data["updated_by_id"] = $request->user()->id;
            $updated = $donor->update($data);
            if (!$updated) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal mengupdate data Donatur', 500);
            }
            DB::commit();
            return ResponseFormatter::success([
                'id' => $donor->id,
                'data' => $donor
            ],'Berhasil mengupdate Donatur');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function destroy (Request $request, $id) {
        DB::beginTransaction();
        try {
            $donor = Donor::find($id);
            if (!$donor) {
                return ResponseFormatter::error(null, 'Data Donatur Tidak ditemukan', 404);
            }
            $deleted = $donor->delete();
            if (!$deleted) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menghapus data donatur', 500);
            }
            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Donatur');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}