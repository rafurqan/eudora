<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;

class WablasController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|array|min:1',
            'phone.0' => 'required|string',
            'message' => 'required|string',
        ]);

        $token = env('WABLAS_TOKEN');
        $secret = env('WABLAS_SECRET');
        $endpoint = 'https://sby.wablas.com/api/send-message';

        $payload = [
            'phone' => $validated['phone'][0],
            'message' => $validated['message'],
        ];

        if ($secret) {
            $payload['secret'] = $secret;
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post($endpoint, $payload);

        return ResponseFormatter::success($response->json(), 'Success');
    }
}
