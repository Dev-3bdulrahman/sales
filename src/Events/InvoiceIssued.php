<?php

namespace Dev3bdulrahman\Sales\Events;

use Dev3bdulrahman\Sales\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceIssued
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public int $userId,
        public int $companyId,
    ) {}
}
