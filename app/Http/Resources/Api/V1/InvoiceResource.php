<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'title'          => $this->title,
            'student'        => $this->whenLoaded('student', fn() => [
                'id'   => $this->student->id,
                'name' => $this->student->full_name,
            ]),
            'session_id'     => $this->session_id,
            'total_amount'   => (float) $this->total_amount,
            'discount_amount'=> (float) $this->discount_amount,
            'tax_amount'     => (float) $this->tax_amount,
            'amount'         => (float) $this->amount,
            'amount_paid'    => (float) $this->amount_paid,
            'amount_due'     => (float) $this->amount_due,
            'status'         => $this->status,
            'is_overdue'     => $this->is_overdue,
            'due_date'       => $this->due_date?->format('Y-m-d'),
            'billing_month'  => $this->billing_month,
            'billing_year'   => $this->billing_year,
            'is_auto_generated' => $this->is_auto_generated,
            'items'          => $this->whenLoaded('items', fn() =>
                $this->items->map(fn($item) => [
                    'description'  => $item->description,
                    'unit_price'   => (float) $item->unit_price,
                    'quantity'     => $item->quantity,
                    'discount'     => (float) $item->discount_amount,
                    'tax'          => (float) $item->tax_amount,
                    'total'        => (float) $item->total,
                ])
            ),
            'payments'       => $this->whenLoaded('payments', fn() =>
                $this->payments->map(fn($p) => [
                    'id'             => $p->id,
                    'receipt_number' => $p->receipt_number,
                    'amount'         => (float) $p->amount,
                    'payment_date'   => $p->payment_date?->format('Y-m-d'),
                    'gateway'        => $p->gateway,
                ])
            ),
            'installments'   => $this->whenLoaded('installments', fn() =>
                $this->installments->map(fn($i) => [
                    'number'   => $i->installment_number,
                    'amount'   => (float) $i->amount,
                    'due_date' => $i->due_date?->format('Y-m-d'),
                    'status'   => $i->status,
                ])
            ),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
