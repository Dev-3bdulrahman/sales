<?php

namespace Dev3bdulrahman\Sales\Listeners;

use App\Services\AuditLogService;
use Dev3bdulrahman\Sales\Events\InvoiceIssued;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogInvoiceIssued implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Handle the InvoiceIssued event.
     */
    public function handle(InvoiceIssued $event): void
    {
        try {
            $this->auditLogService->log(
                action: 'invoice_issued',
                companyId: $event->companyId,
                userId: $event->userId,
                model: $event->invoice,
                oldValues: null,
                newValues: [
                    'invoice_id' => $event->invoice->id,
                    'invoice_number' => $event->invoice->invoice_number,
                    'customer_id' => $event->invoice->customer_id,
                    'grand_total' => $event->invoice->grand_total,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('LogInvoiceIssued: Failed to log invoice issued.', [
                'error' => $e->getMessage(),
                'invoice_id' => $event->invoice->id ?? null,
                'user_id' => $event->userId ?? null,
            ]);
        }
    }
}
