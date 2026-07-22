<?php

namespace Dev3bdulrahman\Sales\Events;

use Dev3bdulrahman\Sales\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesOrderConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SalesOrder $salesOrder,
        public int $userId,
        public int $companyId,
    ) {}
}
