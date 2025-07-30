<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Invoice;
use App\Models\MessageTemplate;
use Carbon\Carbon;

class FonnteController extends Controller
{

    private function renderMessageTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }

        return $template;
    }

    // untuk test aja
    public function blast(Request $req)
    {
        $req->validate([
            'phones' => 'required|array',
            'message' => 'required|string',
        ]);

        $token = config('services.fonnte.token');
        $base = config('services.fonnte.base_url');

        $target = implode(',', $req->phones);

        $payload = [
            'target' => $target,
            'message' => $req->message,
            'countryCode' => '62',
        ];

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post("{$base}/send", $payload);

        return response()->json($response->json(), $response->status());
    }

    public function sendSingle(Request $req)
    {
        $validated = $req->validate([
            'phones' => 'required|array|min:1',
            'phones.0' => 'required|string',
            'invoice_id' => 'nullable|string|exists:invoice,id',
            'message' => 'nullable|string',
        ]);

        $invoice = Invoice::with('entity')->findOrFail($validated['invoice_id']);

        $template = MessageTemplate::where('name', 'invoice_reminder')->firstOrFail();

        $message = $this->renderMessageTemplate($template->body, [
            'name' => $invoice->entity->full_name,
            'code' => $invoice->code,
            'total' => number_format($invoice->total, 0, ',', '.'),
            'due_date' => Carbon::parse($invoice->due_date)->translatedFormat('d F Y'),
        ]);

        $token = config('services.fonnte.token');
        $base = config('services.fonnte.base_url');

        $payload = [
            'target' => $validated['phones'][0],
            'message' => $message,
            'countryCode' => '62',
        ];

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post("{$base}/send", $payload);

        $responseData = $response->json();

        if (($responseData['status'] ?? false) === true) {
            $invoice->update(['delivered_wa' => true]);

            return response()->json([
                'success' => true,
                'message' => 'WA berhasil dikirim',
                'response' => $responseData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'WA gagal dikirim',
            'response' => $responseData
        ], 400);
    }


    public function sendBulk(Request $req)
    {
        $validated = $req->validate([
            'phones' => 'required|array|min:1',
            'phones.*' => 'required|string',
            'message' => 'required|string',
            'invoice_ids' => 'nullable|array',
            'invoice_ids.*' => 'string|exists:invoice,id',
        ]);

        $token = config('services.fonnte.token');
        $base = config('services.fonnte.base_url');

        $target = implode(',', $validated['phones']);

        $payload = [
            'target' => $target,
            'message' => $validated['message'],
            'countryCode' => '62',
        ];

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post("{$base}/send", $payload);

        $responseData = $response->json();

        if (($responseData['status'] ?? false) === true) {
            if (!empty($validated['invoice_ids'])) {
                Invoice::whereIn('id', $validated['invoice_ids'])->update([
                    'delivered_wa' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'WA berhasil dikirim ke semua nomor',
                'response' => $responseData
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'WA gagal dikirim',
            'response' => $responseData
        ], 400);
    }

}
