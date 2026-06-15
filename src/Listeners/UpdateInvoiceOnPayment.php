<?php

namespace Dev3bdulrahman\Sales\Listeners;

use Dev3bdulrahman\Sales\Events\PaymentReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateInvoiceOnPayment implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Handle the PaymentReceived event.
     *
     * Recalculates invoice paid_amount from sum of all payments
     * and updates status accordingly.
     */
    public function handle(PaymentReceived $event): void
    {
        try {
            $invoice = $event->invoice;

            $paidAmount = $invoice->payments()->sum('amount');

            $status = $invoice->status;
            if ($paidAmount >= $invoice->grand_total) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            }

            $invoice->update([
                'paid_amount' => $paidAmount,
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('UpdateInvoiceOnPayment: Failed to update invoice.', [
                'error' => $e->getMessage(),
                'invoice_id' => $event->invoice->id ?? null,
                'payment_id' => $event->payment->id ?? null,
            ]);
        }
    }
}
