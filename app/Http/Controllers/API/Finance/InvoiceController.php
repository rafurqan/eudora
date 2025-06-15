<?php

namespace App\Http\Controllers\API\Finance;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;


class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoice = Invoice::orderBy('created_at', 'desc')->get();
        
        if ($invoice->isEmpty()) {
            return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
        }
        return ResponseFormatter::success($invoice, 'Data Invoice Ditemukan');
    }

    function generateInvoiceCode(): JsonResponse
    {
        $today = Carbon::today();
        $year = $today->format('Y');
        $month = $today->format('m');
        $day = $today->format('d');

        // Hitung jumlah invoice yang dibuat di bulan ini
        $count = DB::table('invoice')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $code = "INV-{$year}-{$month}-{$day}-{$sequence}";

        $sequence = $count + 1;

        return ResponseFormatter::success(['invoice_code' => $code], 'Invoice code generated successfully');
    }

    public function store(CreateInvoiceRequest $request)
    {
        DB::beginTransaction();

        try {
            // Mapping entity_type menjadi nama class model
            $entityClassMap = [
                'student' => \App\Models\Student::class,
                'prospective_student' => \App\Models\ProspectiveStudent::class,
            ];

            $entityTypeInput = $request->input('entity_type');
            $entityId = $request->input('entity_id');

            // Validasi entity_type yang diizinkan
            if (!isset($entityClassMap[$entityTypeInput])) {
                return ResponseFormatter::error(null, 'Jenis entity tidak valid', 422);
            }

            $entityClass = $entityClassMap[$entityTypeInput];
            $entity = $entityClass::findOrFail($entityId);

            // Buat invoice melalui relasi morphMany
            $invoice = $entity->invoices()->create([
                'code' => $request->invoice_number,
                'student_name' => $request->student_name,
                'student_type' => $request->student_type,
                'class_id' => $request->class,
                'class_name' => $request->class_name,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'invoice_type' => $request->invoice_type,
                'notes' => $request->notes,
                'status' => 'draft',
                'total' => collect($request->selected_items)->sum('price'),
                'created_by_id' => $request->user()->id,
            ]);

            foreach ($request->selected_items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $item['service_id'],
                    'amount_rate' => $item['price'],
                    'rate_id' => $item['id'], // ini adalah rate_id
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
       
    }

    public function destroy($id)
    {
    
    }

}
