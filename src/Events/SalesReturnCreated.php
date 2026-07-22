<?php

namespace Dev3bdulrahman\Sales\Events;

use Dev3bdulrahman\Sales\Models\SalesReturn;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesReturnCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SalesReturn $salesReturn,
        public int $userId,
        public int $companyId,
    ) {}
}
