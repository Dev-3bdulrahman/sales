<?php

namespace Dev3bdulrahman\Sales\Services;

use Dev3bdulrahman\Sales\Models\Invoice;
use Dev3bdulrahman\Sales\Models\InvoiceItem;
use Dev3bdulrahman\Sales\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * List invoices with search and filters.
     */
    public function listInvoices(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Invoice::query()->with(['customer', 'creator', 'order']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new Invoice.
     */
    public function createInvoice(array $data, array $items = []): Invoice
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0.0000;
            $taxTotal = 0.0000;
            $discountTotal = 0.0000;

            $itemsToCreate = [];
            foreach ($items as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['unit_price'] ?? 0);
                $discount = (float) ($item['discount_amount'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);

                $itemSub = $qty * $price;
                $itemTax = ($itemSub - $discount) * ($taxRate / 100);
                $itemTotal = $itemSub - $discount + $itemTax;

                $subtotal += $itemSub;
                $discountTotal += $discount;
                $taxTotal += $itemTax;

                $itemsToCreate[] = [
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTax,
                    'discount_amount' => $discount,
                    'total' => $itemTotal,
                ];
            }

            $data['subtotal'] = $subtotal;
            $data['discount_total'] = $discountTotal;
            $data['tax_total'] = $taxTotal;
            $data['grand_total'] = $subtotal - $discountTotal + $taxTotal;
            $data['paid_amount'] = (float) ($data['paid_amount'] ?? 0);
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            // Set default status based on paid amount
            if ($data['paid_amount'] >= $data['grand_total']) {
                $data['status'] = 'paid';
            } elseif ($data['paid_amount'] > 0) {
                $data['status'] = 'partially_paid';
            } else {
                $data['status'] = 'unpaid';
            }

            $invoice = Invoice::create($data);

            foreach ($itemsToCreate as $itemData) {
                $invoice->items()->create($itemData);
            }

            return $invoice;
        });
    }

    /**
     * Update an existing Invoice.
     */
    public function updateInvoice($id, array $data, array $items = []): Invoice
    {
        return DB::transaction(function () use ($id, $data, $items) {
            $invoice = Invoice::findOrFail($id);

            $subtotal = 0.0000;
            $taxTotal = 0.0000;
            $discountTotal = 0.0000;

            $invoice->items()->delete();

            foreach ($items as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['unit_price'] ?? 0);
                $discount = (float) ($item['discount_amount'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);

                $itemSub = $qty * $price;
                $itemTax = ($itemSub - $discount) * ($taxRate / 100);
                $itemTotal = $itemSub - $discount + $itemTax;

                $subtotal += $itemSub;
                $discountTotal += $discount;
                $taxTotal += $itemTax;

                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTax,
                    'discount_amount' => $discount,
                    'total' => $itemTotal,
                ]);
            }

            $data['subtotal'] = $subtotal;
            $data['discount_total'] = $discountTotal;
            $data['tax_total'] = $taxTotal;
            $data['grand_total'] = $subtotal - $discountTotal + $taxTotal;

            $paid = (float) ($data['paid_amount'] ?? $invoice->paid_amount);
            if ($paid >= $data['grand_total']) {
                $data['status'] = 'paid';
            } elseif ($paid > 0) {
                $data['status'] = 'partially_paid';
            } else {
                $data['status'] = 'unpaid';
            }

            $invoice->update($data);

            return $invoice;
        });
    }

    /**
     * Delete an Invoice.
     */
    public function deleteInvoice($id): bool
    {
        $invoice = Invoice::findOrFail($id);
        return $invoice->delete();
    }

    /**
     * Record a Payment on an Invoice.
     */
    public function recordPayment($invoiceId, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoiceId, $paymentData) {
            $invoice = Invoice::findOrFail($invoiceId);

            $paymentData['company_id'] = $invoice->company_id;
            $paymentData['branch_id'] = $invoice->branch_id;
            $paymentData['invoice_id'] = $invoice->id;
            $paymentData['payment_number'] = $paymentData['payment_number'] ?? 'PAY-' . strtoupper(uniqid());
            $paymentData['payment_date'] = $paymentData['payment_date'] ?? now()->format('Y-m-d');
            $paymentData['created_by'] = auth()->id() ?? $invoice->created_by;

            $payment = Payment::create($paymentData);

            // Update Invoice paid amount & status
            $newPaidAmount = $invoice->paid_amount + $payment->amount;
            $status = 'unpaid';

            if ($newPaidAmount >= $invoice->grand_total) {
                $status = 'paid';
            } elseif ($newPaidAmount > 0) {
                $status = 'partially_paid';
            }

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
            ]);

            return $payment;
        });
    }
}
