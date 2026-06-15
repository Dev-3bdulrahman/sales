<?php

namespace Dev3bdulrahman\Sales\Events;

use Dev3bdulrahman\Sales\Models\Invoice;
use Dev3bdulrahman\Sales\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public Invoice $invoice,
        public int $userId,
        public int $companyId,
    ) {}
}
