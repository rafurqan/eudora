<?php
namespace App\Http\Controllers\API\ProspectiveStudent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrationCodeReservation;
use App\Helpers\ResponseFormatter;

class RegistrationCodeController extends Controller
{
    public function getNext()
    {
        $user = auth()->user();
        if (!$user) {
            return ResponseFormatter::error(
                data: null,
                message: 'Unauthorized',
                code: 401
            );
        }

        $now = now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $prefix = "REG-$year-$month-";

        try {
            $code = DB::transaction(function () use ($prefix, $user) {
                $last = RegistrationCodeReservation::where('registration_code', 'like', "$prefix%")
                    ->orderByDesc('registration_code')
                    ->lockForUpdate()
                    ->first();

                $lastNumber = 0;
                if ($last) {
                    $lastNumber = (int)substr($last->registration_code, -4);
                }

                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                $newCode = $prefix . $nextNumber;

                $reservation = RegistrationCodeReservation::create([
                    'registration_code' => $newCode,
                    'reserved_at' => now(),
                    'used' => false,
                    'created_by_id' => $user->id,
                ]);

                return $reservation;
            });

            return ResponseFormatter::success([
                'id' => $code->id,
                'registration_code' => $code->registration_code,
            ], 'Success generate registration code');
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                data: null,
                message: 'Failed to generate registration code',
                code: 500
            );
        }
    }
}

