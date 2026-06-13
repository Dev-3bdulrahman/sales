<?php

namespace Dev3bdulrahman\Sales\Services;

use Dev3bdulrahman\Sales\Models\SalesCommission;
use Dev3bdulrahman\Sales\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesCommissionService
{
    /**
     * List commissions.
     */
    public function listCommissions(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SalesCommission::query()->with(['user', 'invoice', 'order']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Calculate and record commission for an invoice.
     */
    public function calculateCommission(int $invoiceId, float $rate = 5.00): ?SalesCommission
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // Calculate commission on subtotal
        $amount = $invoice->subtotal * ($rate / 100);

        return SalesCommission::create([
            'company_id' => $invoice->company_id,
            'user_id' => $invoice->created_by,
            'invoice_id' => $invoice->id,
            'sales_order_id' => $invoice->sales_order_id,
            'amount' => $amount,
            'rate' => $rate,
            'status' => 'pending',
            'notes' => __('Commission calculated for invoice #') . $invoice->invoice_number,
        ]);
    }
}
