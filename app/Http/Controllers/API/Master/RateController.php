<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRateRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Models\Rate;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;

class RateController extends Controller
{
    // public function index()
    // {
    //     $rate = Rate::join('services', 'rates.service_id', '=', 'services.id')
    //                 ->leftJoin('education_levels', 'rates.program_id', '=', 'education_levels.id')
    //                 ->where(function ($query) {
    //                     $query->whereJsonLength('rates.child_ids', 0)
    //                         ->orWhereNull('rates.child_ids');
    //                 })
    //                 ->orderBy('rates.created_at', 'desc')
    //                 ->select('rates.*', 'services.name as service_name', 'education_levels.name as program')
    //                 ->get();

    //     return ResponseFormatter::success($rate, 'List Tarif');
    // }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $rate = Rate::join('services', 'rates.service_id', '=', 'services.id')
                    ->leftJoin('education_levels', 'rates.program_id', '=', 'education_levels.id')
                    ->where(function ($query) {
                        $query->whereJsonLength('rates.child_ids', 0)
                            ->orWhereNull('rates.child_ids');
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
                    ->get();

        return ResponseFormatter::success($rate, 'List Tarif');
    }


    public function store (CreateRateRequest $request)
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

            // Step 2: Generate kode berdasarkan category
            $category = $data['category'];
            $prefixMap = [
                '1' => 'REG-',   // Registrasi
                '2' => 'BOK-',   // Buku
                '3' => 'SRG-',   // Seragam
                '4' => 'SPP-',   // SPP (tetap)
                '5' => 'EXM-',   // Exam
                '6' => 'EVT-',   // Event/Kegiatan
                '7' => 'TSL-',   // Test/Ujian (Test Scholarly)
                '8' => 'GRD-',   // Graduation
                '9' => 'MSC-',   // Misc (Lainnya)
            ];

            $prefix = $prefixMap[$category] ?? 'MSC';

            $count = Rate::withTrashed()->where('category', $category)->count();
            $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            $generatedCode = $prefix . $nextNumber;

            // Step 3: Simpan ke table rates
            $rateId = uuid_create();
            $rateData = [
                'id' => $rateId,
                'service_id' => $serviceId,
                'child_ids' => $data['child_ids'] ?? [],
                'program_id' => $data['program_id'] ?? null,
                'price' => $data['price'],
                'is_active' => $data['is_active'],
                'created_by_id' => $userId,
                'created_at' => now(),
                'code' => $generatedCode,
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'applies_to' => $data['applies_to'] ?? null,
            ];

            $rate = Rate::create($rateData);

            DB::commit();

            return ResponseFormatter::success([
                'rate_id' => $rateId,
                'rate' => $rate,
                'service_id' => $serviceId,
                'service' => $service
            ], 'Berhasil membuat Service dan Tarif');
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

            // Update kode berdasarkan category
            $category = $data['category'];
            $prefixMap = [
                '1' => 'REG-',   // Registrasi
                '2' => 'BOK-',   // Buku
                '3' => 'SRG-',   // Seragam
                '4' => 'SPP-',   // SPP (tetap)
                '5' => 'EXM-',   // Exam
                '6' => 'EVT-',   // Event/Kegiatan
                '7' => 'TSL-',   // Test/Ujian (Test Scholarly)
                '8' => 'GRD-',   // Graduation
                '9' => 'MSC-',   // Misc (Lainnya)
            ];

            $prefix = $prefixMap[$category] ?? 'MSC-';

            $lastCode = Rate::withTrashed()
                ->where('category', $category)
                ->where('code', 'like', $prefix . '%')
                ->orderBy('code', 'desc')
                ->value('code');

            if ($lastCode) {
                $lastNumber = (int) substr($lastCode, strlen($prefix));
                $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '001';
            }

            $generatedCode = $prefix . $nextNumber;

            // Update service terlebih dahulu
            $serviceName = $data['service_name'] ?? null;
            if ($serviceName) {
                $rate->service->update([
                    'name' => $serviceName,
                    'is_active' => $data['is_active'],
                ]);
            }

            // Update rate
            $rateData = [
                'program_id' => $data['program_id'] ?? null,
                'price' => $data['price'],
                'is_active' => $data['is_active'],
                'created_by_id' => $data["updated_by_id"],
                'created_at' => now(),
                'code' => $generatedCode,
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'applies_to' => $data['applies_to'] ?? null,
            ];

            $updated = $rate->update($rateData);
            if (!$updated) {
                DB::rollBack();
                return ResponseFormatter::error(null, 'Gagal mengupdate data tarif', 500);
            }

            DB::commit();
            return ResponseFormatter::success([
                'id' => $rate->id,
                'data' => $rate
            ], 'Berhasil mengupdate Tarif');

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
            return ResponseFormatter::success(null, 'Berhasil menghapus Tarif');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

}
