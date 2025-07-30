<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary()
    {
        $now = Carbon::now();
        $startOfYear = $now->copy()->startOfYear();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $totalStudentsYear = Student::whereBetween('created_at', [$startOfYear, $now])->count();

        $totalStudentsMonth = Student::whereBetween('created_at', [$startOfMonth, $now])->count();

        $totalInvoiceMonth = Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $totalPaymentMonth = Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $collectionRate = $totalInvoiceMonth > 0
            ? round(($totalPaymentMonth / $totalInvoiceMonth) * 100, 2)
            : 0;

        $totalPendingPayment = Invoice::whereDoesntHave('payments')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        return response()->json([
            'total_students_year' => $totalStudentsYear,
            'total_students_month' => $totalStudentsMonth,
            'total_invoice_month' => $totalInvoiceMonth,
            'total_payment_month' => $totalPaymentMonth,
            'pending_payment' => $totalPendingPayment,
            'collection_rate_percent' => $collectionRate,
        ]);
    }
}

