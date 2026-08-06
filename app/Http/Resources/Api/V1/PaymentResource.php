<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'receipt_number'         => $this->receipt_number,
            'invoice_id'             => $this->invoice_id,
            'invoice_number'         => $this->invoice?->invoice_number,
            'student'                => $this->whenLoaded('invoice', fn() => $this->invoice->student ? [
                'id'   => $this->invoice->student->id,
                'name' => $this->invoice->student->full_name,
            ] : null),
            'amount'                 => (float) $this->amount,
            'payment_date'           => $this->payment_date?->format('Y-m-d'),
            'payment_method'         => $this->payment_method,
            'gateway'                => $this->gateway,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'notes'                  => $this->notes,
            'recorded_by'            => $this->whenLoaded('recordedBy', fn() => [
                'id'   => $this->recordedBy->id,
                'name' => $this->recordedBy->full_name,
            ]),
            'created_at'             => $this->created_at?->toIso8601String(),
        ];
    }
}
