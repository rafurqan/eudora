<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,
            'rate_id' => $this->rate_id,
            'service_id' => $this->rate->service->id ?? null,
            'service_name' => $this->rate->service->name ?? null,
            'price' => $this->amount_rate,
            'frequency' => $this->frequency,
        ];
    }
}
