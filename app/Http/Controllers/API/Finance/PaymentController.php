<?php

namespace App\Http\Controllers\API\Finance;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Models\PaymentCodeReservation;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page',0);

        $query = Invoice::with(['entity', 'payment', 'studentClass'])
            ->leftJoin('payment', 'invoice.id', '=', 'payment.invoice_id')
            ->select('invoice.*')
            ->orderBy('invoice.created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $upperSearch = strtoupper($search);

                $q->whereRaw('UPPER(invoice.code) LIKE ?', ["%{$upperSearch}%"])
                ->orWhereRaw('UPPER(payment.code) LIKE ?', ["%{$upperSearch}%"])
                ->orWhereRaw('UPPER(payment.payment_method) LIKE ?', ["%{$upperSearch}%"]);
            });

            $query->orWhereHasMorph('entity', [\App\Models\Student::class, \App\Models\ProspectiveStudent::class], function ($q) use ($search) {
            $q->where('full_name', 'like', "%$search%");
            });

            $query->orWhereHas('studentClass', function ($q2) use ($search) {
                $q2->where('name', 'like', "%$search%");
            });
        }

        if ($status === 'unpaid') {
            $query->where(function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('payment.status', '!=', 'paid')
                             ->orWhereNull('payment.status');
                })
                ->orWhereNull('payment.status');
            });
        } elseif ($status && $status !== 'Semua Status') {
            $query->where('payment.status', $status);
        }

        $result = $perPage > 0 ? $query->paginate($perPage) : $query->get();

        if ($result->isEmpty()) {
            return ResponseFormatter::error(null, 'Data tidak ditemukan', 404);
        }

        return ResponseFormatter::success($result, 'Data Payment Ditemukan');
    }


    public function statistics()
    {
        $query = Payment::query();

        $totalCount = $query->count();
        $totalAmount = $query->sum('nominal_payment');

        // Status berdasarkan enum atau nilai tetap
        $statuses = ['paid', 'unpaid', 'pending', 'late'];

        // Looping semua status untuk dapatkan count & amount
        $statusData = [];
        foreach ($statuses as $status) {
            $count = Payment::where('status', $status)->count();
            $amount = Payment::where('status', $status)->sum('nominal_payment');

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

        return ResponseFormatter::success($data, 'Statistik Payment ditemukan');
    }

    public function show($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return ResponseFormatter::error(null, 'Pembayaran tidak ditemukan', 404);
        }

        return ResponseFormatter::success($payment, 'Pembayaran ditemukan');
    }

    public function store(CreatePaymentRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            $invoice = Invoice::findOrFail($data['invoice_id']);

            $status = ((int) $data['nominal_payment'] === (int) $invoice->total) ? 'paid' : 'partial';

            //generate code payment
            $today = now();
            $prefixMonth = $today->format('Y-m'); 
            $date = $today->format('Y-m-d');      
            $prefix = "PAY-{$prefixMonth}";       

            $lastCode = DB::table('payment_code_reservation')
                ->where('code', 'like', "{$prefix}-%")
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $lastNumber = $lastCode
                ? (int) substr($lastCode, -4)
                : 0;

            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $newCode = "PAY-{$date}-{$newNumber}";

            PaymentCodeReservation::create([
                'code' => $newCode,
                'reserved_at' => now(),
            ]);

            $payment = $invoice->payments()->create([
                'code'              => $newCode,
                'payment_method'    => $data['payment_method'],
                'nominal_payment'   => $data['nominal_payment'] ?? 0,
                'payment_date'      => $data['payment_date'],
                'notes'             => $data['notes'] ?? null,
                'bank_name'         => $data['bank_name'] ?? ($data['bank_details']['bank_name'] ?? null),
                'account_number'    => $data['account_number'] ?? ($data['bank_details']['account_number'] ?? null),
                'account_name'      => $data['account_name'] ?? ($data['bank_details']['account_holder'] ?? null),
                'reference_number'  => $data['reference_number'] ?? ($data['bank_details']['reference_number'] ?? null),
                'id_grant'          => $data['id_grant'] ?? $data['grant_id'] ?? null,
                'grant_amount'      => $data['grant_amount'] ?? 0,
                'status'            => $status,
                'created_by_id'     => $request->user()->id ?? null,
            ]);

            $invoice->status = $status;
            $invoice->save();

            DB::commit();
            return ResponseFormatter::success($payment, 'Pembayaran berhasil disimpan');
        
        } catch (\Throwable $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }


    public function update(UpdatePaymentRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            $payment = Payment::findOrFail($id);
            $invoice = Invoice::findOrFail($data['invoice_id']);

            // Tentukan status baru berdasarkan nominal vs total invoice
            $status = ((int) $data['nominal_payment'] === (int) $invoice->total) ? 'paid' : 'partial';

            $payment->update([
                'payment_method'    => $data['payment_method'],
                'nominal_payment'   => $data['nominal_payment'] ?? 0,
                'payment_date'      => $data['payment_date'],
                'notes'             => $data['notes'] ?? null,
                'bank_name'         => $data['bank_name'] ?? null,
                'account_number'    => $data['account_number'] ?? null,
                'account_name'      => $data['account_name'] ?? null,
                'reference_number'  => $data['reference_number'] ?? null,
                'id_grant'          => $data['id_grant'] ?? null,
                'grant_amount'      => $data['grant_amount'] ?? 0,
                'status'            => $status,
                'updated_by_id'     => $request->user()->id ?? null,
            ]);

            DB::commit();
            return ResponseFormatter::success($payment, 'Pembayaran berhasil diperbarui');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);

            $payment->status = null;
            $payment->save();
            $payment->delete();

            return ResponseFormatter::success(null, 'Data berhasil dihapus (soft delete)');
        } catch (\Throwable $e) {
            return ResponseFormatter::error(null, 'Gagal menghapus data: ' . $e->getMessage(), 500);
        }
    }

}
