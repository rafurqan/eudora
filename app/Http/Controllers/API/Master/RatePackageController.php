<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRatePackageRequest;
use App\Http\Requests\UpdateRatePackageRequest;
use App\Models\RatePackage;
use App\Models\Rate;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Container\Attributes\Log as AttributesLog;

class RatePackageController extends Controller
{
    // public function index()
    // {
    //     $rate = RatePackage::join('services', 'rates.service_id', '=', 'services.id')
    //     ->whereNotNull('rates.child_ids')
    //     ->whereJsonLength('rates.child_ids', '>', 0)
    //     ->where(function ($query) {
    //         $query->whereJsonDoesntContain('rates.child_ids', ['']);
    //     })
    //     ->orderBy('rates.created_at', 'desc')
    //     ->select('rates.*', 'services.name as service_name')
    //     ->get();
    //     return ResponseFormatter::success($rate, 'List Paket Tarif');
    // }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $packages = RatePackage::join('services', 'rates.service_id', '=', 'services.id')
                    ->leftJoin('education_levels', 'rates.program_id', '=', 'education_levels.id')
                    ->where(function ($query) {
                        $query->whereJsonLength('rates.child_ids', '>',  0);
                    })
                    ->when($search, function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('services.name', 'ilike', "%{$search}%")
                            ->orWhere('rates.code', 'ilike', "%{$search}%")
                            ->orWhere('rates.description', 'ilike', "%{$search}%");
                        });
                    })
                    ->orderBy('rates.created_at', 'desc')
                    ->select('rates.*', 'services.name as service_name', 'education_levels.name as program')
                    ->whereNull('rates.deleted_at') // Adjust query for soft deletes
                    ->get();

        // Hitung total_price dari child_ids
        $packages->transform(function ($item) {
            $childIds = is_array($item->child_ids) ? $item->child_ids : [];

            $totalPrice = !empty($childIds)
                ? RatePackage::whereIn('id', $childIds)->whereNull('deleted_at')->sum('price') // Adjust query for soft deletes
                : 0;
            $item->total_price = $totalPrice;
            return $item;
        });

        return ResponseFormatter::success($packages, 'List Paket Tarif');
    }

    public function store(CreateRatePackageRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            $userId = $request->user()->id;

            // Step 1: Simpan ke table services terlebih dahulu
            $serviceId = uuid_create();
            $service = Service::create([
                'id' => $serviceId,
                'name' => $data['service_name'],
                'is_active' => $data['is_active'],
                'created_at' => now(),
                'created_by_id' => $userId,
            ]);
            
            // Step 2: Simpan ke table rates
            $childIds = $request->input('rates', []); // Ambil dari request asli, bukan dari validated
            $rateId = uuid_create();


            $totalPaket = RatePackage::withTrashed()->whereJsonLength('child_ids', '>', 0)->count();
            $kode = 'PKG-' . str_pad($totalPaket + 1, 3, '0', STR_PAD_LEFT);

            $rateData = [
                'id' => $rateId,
                'service_id' => $serviceId,
                'child_ids' => $childIds,
                'program_id' => $data['program_id'] ?? null,
                'price' => $data['price'],
                'is_active' => $data['is_active'],
                'created_by_id' => $userId,
                'created_at' => now(),
                'code' => $kode,
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'applies_to' => $data['applies_to'] ?? null,
            ];

            $rate = RatePackage::create($rateData);

            if (!$rate) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal menyimpan data paket tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success([
                'rate_id' => $rateId,
                'rate' => $rate,
                'service_id' => $serviceId,
                'service' => $service
            ], 'Berhasil membuat Service dan Paket Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    
    public function update(UpdateRatePackageRequest $request, $id)
    {   
        try {
            DB::beginTransaction();
            
            $ratePackage = RatePackage::find($id);
            if (!$ratePackage) {
                return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
            }

            $data = $request->validated();
            $userId = $request->user()->id;
            $data["updated_by_id"] = $userId;

            // Update service terlebih dahulu
            $serviceId = $ratePackage->service_id;
            $serviceName = $data['service_name'] ?? null;
            if ($serviceName) {
                Service::where('id', $serviceId)->update([
                    'name' => $serviceName,
                    'is_active' => $data['is_active'] ?? $ratePackage->is_active,
                    'updated_by_id' => $userId,
                ]);
            }

            $childIds = array_map('trim', $request->input('rates', []));
            $data['child_ids'] = $childIds; 

            // Update rate package
            $updated = $ratePackage->update($data);
            if (!$updated) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal mengupdate data paket tarif', 500);
            }

            // Refresh model agar data terbaru termasuk child_ids terambil
            $ratePackage->refresh();

            DB::commit();
            return ResponseFormatter::success([
                'rate_id' => $ratePackage->id,
                'rate' => $ratePackage,
                'service_id' => $serviceId,
            ], 'Berhasil mengupdate Paket Tarif');
            
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

            $rate->delete(); // soft delete

            $rate->service?->delete(); // soft delete relasi service jika ada

            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Paket Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
