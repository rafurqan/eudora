<?php

namespace App\Http\Controllers\API\Finance;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\InvoiceCodeReservation;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 0);

        $query = Invoice::with('entity', 'studentClass', 'payment')->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                ->orWhere('notes', 'like', "%$search%");
            });

            $query->orWhereHasMorph('entity', [\App\Models\Student::class, \App\Models\ProspectiveStudent::class], function ($q) use ($search) {
            $q->where('full_name', 'like', "%$search%");
            });
        }

        if ($status && $status !== 'Semua Status') {
            if ($status === 'unpaid') {
                $query->whereIn('status', ['unpaid', 'partial']);
            } else {
                $query->where('status', $status);
            }
        }


        if ($perPage > 0) {
            $invoice = $query->paginate($perPage);
        } else {
            $invoice = $query->get();
        }

        if ($invoice->isEmpty()) {
            return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
        }

        return ResponseFormatter::success($invoice, 'Data Invoice Ditemukan');
    }

    public function statistics()
    {
        $query = Invoice::query();

        $totalCount = $query->count();
        $totalAmount = $query->sum('total');

        // Status berdasarkan enum atau nilai tetap
        $statuses = ['Menunggu Pembayaran', 'Terlambat', 'Lunas'];

        // Looping semua status untuk dapatkan count & amount
        $statusData = [];
        foreach ($statuses as $status) {
            $count = Invoice::where('status', $status)->count();
            $amount = Invoice::where('status', $status)->sum('total');

            $statusData[] = [
                'status' => $status,
                'count' => $count,
                'amount' => $amount,
            ];
        }

        $data = [
            'total' => [
                'count' => $totalCount,
                'amount' => $totalAmount,
            ],
            'per_status' => $statusData,
        ];

        return ResponseFormatter::success($data, 'Statistik invoice ditemukan');
    }

    public function show($id)
    {
        $invoice = Invoice::with([
            'items.rate.service', // eager load nested relation
            'studentClass',
            'entity'
        ])->find($id);

        if (!$invoice) {
            return ResponseFormatter::error(null, 'Invoice tidak ditemukan', 404);
        }

        $invoice->selected_items = $invoice->items->map(function ($item) {
            return [
                'id' => $item->rate_id,
                'rate_id' => $item->rate_id,
                'service_id' => $item->rate->service->id ?? null,
                'service_name' => $item->rate->service->name ?? null,
                'price' => $item->amount_rate,
                'frequency' => $item->frequency,
            ];
        });

        return ResponseFormatter::success($invoice, 'Detail invoice ditemukan');
    }


    public function generateInvoiceCode(): JsonResponse
    {
        return DB::transaction(function () {
            $today = now();
            $prefixMonth = $today->format('Y-m');
            $date = $today->format('Y-m-d');
            $prefix = "INV-{$prefixMonth}";

            // Ambil kode terakhir di bulan ini
            $lastCode = DB::table('invoice_code_reservations')
                ->where('code', 'like', "{$prefix}-%")
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $lastNumber = $lastCode
                ? (int) substr($lastCode, -4)
                : 0;

            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $newCode = "INV-{$date}-{$newNumber}";

            InvoiceCodeReservation::create([
                'code' => $newCode,
                'reserved_at' => now(),
            ]);

            return ResponseFormatter::success(['invoice_code' => $newCode], 'Invoice code generated successfully');
        });
    }


    public function store(CreateInvoiceRequest $request)
    {
        DB::beginTransaction();

        try {
            // Mapping entity_type ke model
            $entityClassMap = [
                'student' => \App\Models\Student::class,
                'prospective_student' => \App\Models\ProspectiveStudent::class,
            ];

            $entityTypeInput = $request->input('entity_type');
            $entityId = $request->input('entity_id');

            if (!isset($entityClassMap[$entityTypeInput])) {
                return ResponseFormatter::error(null, 'Jenis entity tidak valid', 422);
            }

            $entityClass = $entityClassMap[$entityTypeInput];
            $entity = $entityClass::findOrFail($entityId);

            // === GENERATE CODE DI SINI SECARA TRANSAKSI ===
            $today = now();
            $prefixMonth = $today->format('Y-m');
            $date = $today->format('Y-m-d');
            $prefix = "INV-{$prefixMonth}";

            // Ambil kode terakhir di bulan ini
            $lastCode = DB::table('invoice_code_reservations')
                ->where('code', 'like', "{$prefix}-%")
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $lastNumber = $lastCode
                ? (int) substr($lastCode, -4)
                : 0;

            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $newCode = "INV-{$date}-{$newNumber}";

            InvoiceCodeReservation::create([
                'code' => $newCode,
                'reserved_at' => now(),
            ]);

            // create invoice
            $invoice = $entity->invoices()->create([
                'code' => $newCode,
                'student_name' => $request->student_name,
                'student_type' => $request->student_type,
                'student_class' => $request->class,
                'class_name' => $request->class_name,
                'publication_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'invoice_type' => $request->invoice_type,
                'notes' => $request->notes,
                'status' => 'unpaid',
                'total' => collect($request->selected_items)->sum(fn($item) => $item['price'] * $item['frequency']),
                'created_by_id' => $request->user()->id,
            ]);

            // create invoice items
            foreach ($request->selected_items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $item['service_id'],
                    'amount_rate' => $item['price'],
                    'rate_id' => $item['id'],
                    'frequency' => $item['frequency'],
                ]);
            }

            DB::commit();
            return ResponseFormatter::success($invoice, 'Invoice Berhasil Dibuat');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }


    public function update(UpdateInvoiceRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $invoice = Invoice::with('items')->find($id);
            if (!$invoice) {
                return ResponseFormatter::error(null, 'Invoice tidak ditemukan', 404);
            }

            // Update field utama invoice
            $invoice->update([
                'student_name' => $request->student_name,
                'student_type' => $request->student_type,
                'student_class' => $request->class,
                'class_name' => $request->class_name,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'invoice_type' => $request->invoice_type,
                'notes' => $request->notes,
                'total' => collect($request->selected_items)->sum(fn($item) => $item['price'] * $item['frequency']),
                'updated_by_id' => $request->user()->id,
            ]);

            // Hapus semua invoice item lama (soft delete)
            $invoice->items()->delete();

            // Tambahkan ulang item baru
            foreach ($request->selected_items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $item['service_id'],
                    'amount_rate' => $item['price'],
                    'rate_id' => $item['id'],
                    'frequency' => $item['frequency'],
                ]);
            }

            DB::commit();
            return ResponseFormatter::success($invoice->load('items'), 'Invoice berhasil diperbarui');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // 1. Ambil invoice beserta relasi item-nya
            $invoice = Invoice::with('items')->find($id);
            if (!$invoice) {
                return ResponseFormatter::error(null, 'Data Invoice Tidak ditemukan', 404);
            }

            // 2. Soft delete semua item terkait
            foreach ($invoice->items as $item) {
                $item->delete();
            }

            // 3. Soft delete invoice
            $invoice->delete();

            DB::commit();
            return ResponseFormatter::success(null, 'Berhasil menghapus Invoice');

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }


}
