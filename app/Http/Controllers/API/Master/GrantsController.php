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
    public function index(Request $request)
    {
        $search  = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $grant = Grant::select('grants.*')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('donor_name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('grants_name', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('grants.created_at', 'desc')
            ->paginate($perPage);

        if ($grant->isEmpty()) {
            return ResponseFormatter::error(null, 'Data Donasi Kosong', 404);
        }

        return ResponseFormatter::success($grant, 'List Donasi');
    }


    public function store (CreateGrantRequest $request) {
        try {
            DB::beginTransaction();

            $category = $request->donation_type;
            $prefixMap = [
                '1' => 'INV-',  // Individu
                '2' => 'ORG-',   // Organisasi
                '3' => 'GRP-',   // Kelompok
            ];

            $prefix = $prefixMap[$category] ?? 'EXT-';
            $count = Grant::withTrashed()->where('donation_type', $category)->count() + 1;
            $nextNumber = str_pad($count, 3, '0', STR_PAD_LEFT);
            $generatedCode = $prefix . $nextNumber;

            $data = $request->validated();
            $id = uuid_create();
            $data['id'] = $id;
            $data['created_by_id'] = $request->user()->id;
            $data['updated_at'] = null;
            $data['acceptance_date'] = $request->acceptance_date ?? now()->addYear()->toDateString();
            $data['code'] = $generatedCode;
            $data['is_active'] = $request->isActiveCheckbox ? 'Y' : 'N';
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

    public function reset(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $grant = Grant::findOrFail($id);
            $grant->current_reset_version = $grant->current_reset_version + 1;
            $grant->total_used_funds = 0;
            $grant->save();

            DB::commit();

            return ResponseFormatter::success($grant, 'Dana hibah berhasil direset.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Gagal reset dana hibah: ' . $e->getMessage(), 500);
        }
    }



    public function destroy (Request $request, $id) {
        try {
            DB::beginTransaction();

            $grant = Grant::find($id);

            if (!$grant) {
                return ResponseFormatter::error(null, 'Data Donasi Tidak ditemukan', 404);
            }

            $grant->delete();

            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Donasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
