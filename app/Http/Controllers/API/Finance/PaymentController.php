<?php

namespace App\Http\Controllers\API\Finance;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Grant;
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
        $perPage = $request->input('per_page', 0);

        $query = Invoice::with(['entity', 'payment', 'studentClass'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $upperSearch = strtoupper($search);

                $q->whereRaw('UPPER(code) LIKE ?', ["%{$upperSearch}%"])
                ->orWhereHas('payment', function ($sub) use ($upperSearch) {
                    $sub->whereRaw('UPPER(code) LIKE ?', ["%{$upperSearch}%"])
                        ->orWhereRaw('UPPER(payment_method) LIKE ?', ["%{$upperSearch}%"]);
                })
                ->orWhereHasMorph('entity', [\App\Models\Student::class, \App\Models\ProspectiveStudent::class], function ($q2) use ($search) {
                    $q2->where('full_name', 'like', "%{$search}%");
                })
                ->orWhereHas('studentClass', function ($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($status === 'unpaid') {
            $query->where(function ($q) {
                $q->whereDoesntHave('payment')
                ->orWhereHas('payment', function ($subQuery) {
                    $subQuery->where('status', '!=', 'paid');
                });
            });
        } elseif ($status && $status !== 'Semua Status') {
            $query->whereHas('payment', function ($q) use ($status) {
                $q->where('status', $status);
            });
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

        $statuses = ['paid', 'unpaid', 'pending', 'late'];

        $statusData = [];
        foreach ($statuses as $status) {
            $count = Payment::where('status', $status)->count();
            $amount = Payment::where('status', $status)->sum('total_payment');

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

            $status = ((int) $data['total_payment'] === (int) $invoice->total) ? 'paid' : 'partial';

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

            if (!empty($data['id_grant']) || !empty($data['grant_id'])) {
                $grantId = $data['id_grant'] ?? $data['grant_id'];
                $grant = Grant::find($grantId);

                if (!$grant) {
                    return ResponseFormatter::error(null, 'Hibah tidak ditemukan', 404);
                }

                $requestedGrantAmount = (int) ($data['grant_amount'] ?? 0);
                $availableFunds = (int) ($grant->total_funds - $grant->total_used_funds);

                if ($requestedGrantAmount > $availableFunds) {
                    return ResponseFormatter::error(null, "Dana hibah tidak mencukupi. Sisa dana tersedia: Rp " . number_format($availableFunds), 422);
                }
            }

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
                'use_grant'         => !empty($data['id_grant']) || !empty($data['grant_id']) ? 1 : 0,
                'status'            => $status,
                'total_payment'     => $data['total_payment'] ?? 0,
                'created_by_id'     => $request->user()->id ?? null,
            ]);

            $invoice->status = $status;
            $invoice->save();

            $grant = Grant::find($payment->id_grant);

            if (!empty($payment->id_grant)) {
                DB::table('log_grant')->insert([
                    'id'             => (string) Str::uuid(),
                    'grant_id'       => $grant->id,
                    'payment_id'     => $payment->id,
                    'amount_used'    => $data['grant_amount'],
                    'period'         => now()->format('Y-m'),
                    'reset_version'  => $grant->current_reset_version,
                    'created_by_id'  => $request->user()->id ?? null,
                    'used_at'        => now(),
                ]);
            }

            $grantAmount = (int) ($data['grant_amount'] ?? 0);
            if (!empty($payment->id_grant) && $grantAmount > 0) {
                $grant = Grant::find($payment->id_grant);
                if ($grant) {
                    $grant->increment('total_used_funds', $grantAmount);
                }
            }


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

            $status = ((int) $data['total_payment'] === (int) $invoice->total) ? 'paid' : 'partial';

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
                'use_grant'         => !empty($data['id_grant']) ? 1 : 0,
                'status'            => $status,
                'total_payment'     => $data['total_payment'] ?? 0,
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
            $invoice = Invoice::findOrFail($payment['invoice_id']);

            DB::table('history_payment')->insert([
                'id' => (string) Str::uuid(),
                'payment_id'           => $payment->id,
                'invoice_id'           => $payment->invoice_id,
                'code'                 => $payment->code,
                'payment_method'       => $payment->payment_method,
                'nominal_payment'      => $payment->nominal_payment,
                'payment_date'         => $payment->payment_date,
                'notes'                => $payment->notes,
                'bank_name'            => $payment->bank_name,
                'account_number'       => $payment->account_number,
                'account_name'         => $payment->account_name,
                'reference_number'     => $payment->reference_number,
                'id_grant'             => $payment->id_grant,
                'grant_amount'         => $payment->grant_amount,
                'status'               => $payment->status,
                'total_payment'        => $payment->total_payment,
                'deleted_by_id'        => $request->user()->id ?? null,
                'deleted_reason'       => $request->input('reason') ?? null,
                'deleted_at'           => now(),
                'original_created_by_id' => $payment->created_by_id,
                'original_created_at'    => $payment->created_at,
                'original_updated_by_id' => $payment->updated_by_id,
                'original_updated_at'    => $payment->updated_at,
            ]);

            if (!empty($payment->id_grant) && $payment->grant_amount > 0) {
                $grant = Grant::find($payment->id_grant);
                if ($grant) {
                    $grant->decrement('total_used_funds', $payment->grant_amount);
                }
            }

            $payment->status = 'unpaid';
            $payment->save();
            $payment->delete();

            $invoice->status = 'unpaid';
            $invoice->save();

            return ResponseFormatter::success(null, 'Data berhasil dihapus dan dicatat ke riwayat');
        } catch (\Throwable $e) {
            return ResponseFormatter::error(null, 'Gagal menghapus data: ' . $e->getMessage(), 500);
        }
    }

}