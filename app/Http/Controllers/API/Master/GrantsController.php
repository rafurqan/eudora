<?php

namespace App\Http\Controllers\API\Master;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGrantRequest;
use App\Http\Requests\UpdateGrantRequest;
use App\Models\Grant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrantsController extends Controller
{
    public function index()
    {
        $grant = Grant::select(
            'grants.*',
            'donation_types.name as tipe_donasi',
            'donors.name as nama_donatur'
        )
        ->leftJoin('donation_types', 'grants.donation_type_id', '=', 'donation_types.id')
        ->leftJoin('donors', 'grants.donor_id', '=', 'donors.id')
        ->orderBy('grants.created_at', 'desc')
        ->get();

        if ($grant->count() == 0) {
            return ResponseFormatter::error(null, 'Data Donasi Kosong', 404);
        }

        return ResponseFormatter::success($grant, 'List Donasi');
    }

    public function store (CreateGrantRequest $request) {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $id = uuid_create();
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            $data['updated_at'] = null;
            $data['grant_expiration_date'] = $request->grant_expiration_date ?? now()->addYear()->toDateString();
            $grant = Grant::create($data);
            DB::commit();
            return ResponseFormatter::success([
                'id' => $id,
                'data' => $grant
            ], 'Berhasil membuat Donasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function update (UpdateGrantRequest $request, $id) {
        DB::beginTransaction();
        try {
            $grant = Grant::find($id);
            if (!$grant) {
                return ResponseFormatter::error(null, 'Data Donasi Tidak ditemukan', 404);
            }
            $data = $request->validated();
            $data["updated_by_id"] = $request->user()->id;
            $updated = $grant->update($data);
            if (!$updated) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal mengupdate data Donasi', 500);
            }
            DB::commit();
            return ResponseFormatter::success([
                'id' => $grant->id,
                'data' => $grant
            ],'Berhasil mengupdate Donasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    public function destroy (Request $request, $id) {
        DB::beginTransaction();
        try {
            $grant = Grant::find($id);
            if (!$grant) {
                return ResponseFormatter::error(null, 'Data Donasi Tidak ditemukan', 404);
            }
            $deleted = $grant->delete();
            if (!$deleted) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menghapus data donasi', 500);
            }
            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Donasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
