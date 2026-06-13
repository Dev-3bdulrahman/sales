<?php

namespace Dev3bdulrahman\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'invoice_id' => $this->invoice_id,
            'invoice_number' => $this->invoice ? $this->invoice->invoice_number : null,
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date ? $this->payment_date->format('Y-m-d') : null,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'reference_number' => $this->reference_number,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
