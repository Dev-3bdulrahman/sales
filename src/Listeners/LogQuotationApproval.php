<?php

namespace Dev3bdulrahman\Sales\Listeners;

use App\Services\AuditLogService;
use Dev3bdulrahman\Sales\Events\QuotationApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogQuotationApproval implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Handle the QuotationApproved event.
     */
    public function handle(QuotationApproved $event): void
    {
        try {
            $this->auditLogService->log(
                action: 'quotation_approved',
                companyId: $event->companyId,
                userId: $event->userId,
                model: $event->quotation,
                oldValues: null,
                newValues: [
                    'quotation_id' => $event->quotation->id,
                    'quotation_number' => $event->quotation->quotation_number,
                    'customer_id' => $event->quotation->customer_id,
                    'grand_total' => $event->quotation->grand_total,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('LogQuotationApproval: Failed to log quotation approval.', [
                'error' => $e->getMessage(),
                'quotation_id' => $event->quotation->id ?? null,
                'user_id' => $event->userId ?? null,
            ]);
        }
    }
}
