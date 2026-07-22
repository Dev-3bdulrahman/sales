<?php

namespace Dev3bdulrahman\Sales\Events;

use Dev3bdulrahman\Sales\Models\Quotation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Quotation $quotation,
        public int $userId,
        public int $companyId,
    ) {}
}
