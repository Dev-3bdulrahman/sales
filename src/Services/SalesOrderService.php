<?php

namespace Dev3bdulrahman\Sales\Services;

use Dev3bdulrahman\Sales\Models\SalesOrder;
use Dev3bdulrahman\Sales\Models\SalesOrderItem;
use Dev3bdulrahman\Sales\Models\Invoice;
use Dev3bdulrahman\Sales\Models\InvoiceItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    /**
     * List sales orders with search and filters.
     */
    public function listOrders(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SalesOrder::query()->with(['customer', 'creator', 'quotation']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
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
     * Create a new Sales Order.
     */
    public function createOrder(array $data, array $items = []): SalesOrder
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
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            $order = SalesOrder::create($data);

            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });
    }

    /**
     * Update an existing Sales Order.
     */
    public function updateOrder($id, array $data, array $items = []): SalesOrder
    {
        return DB::transaction(function () use ($id, $data, $items) {
            $order = SalesOrder::findOrFail($id);

            $subtotal = 0.0000;
            $taxTotal = 0.0000;
            $discountTotal = 0.0000;

            $order->items()->delete();

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

                $order->items()->create([
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

            $order->update($data);

            return $order;
        });
    }

    /**
     * Delete a Sales Order.
     */
    public function deleteOrder($id): bool
    {
        $order = SalesOrder::findOrFail($id);
        return $order->delete();
    }

    /**
     * Convert Sales Order to Invoice.
     */
    public function convertToInvoice($id, array $additionalData = []): Invoice
    {
        return DB::transaction(function () use ($id, $additionalData) {
            $order = SalesOrder::with('items')->findOrFail($id);

            $invoiceNumber = $additionalData['invoice_number'] ?? 'INV-' . strtoupper(uniqid());
            $invoiceDate = $additionalData['invoice_date'] ?? now()->format('Y-m-d');
            $dueDate = $additionalData['due_date'] ?? now()->addDays(15)->format('Y-m-d');

            // Create Invoice
            $invoice = Invoice::create([
                'company_id' => $order->company_id,
                'branch_id' => $order->branch_id,
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => 'unpaid',
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total,
                'discount_total' => $order->discount_total,
                'grand_total' => $order->grand_total,
                'paid_amount' => 0.0000,
                'notes' => $additionalData['notes'] ?? $order->notes,
                'created_by' => auth()->id() ?? $order->created_by,
            ]);

            // Copy items
            foreach ($order->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'discount_amount' => $item->discount_amount,
                    'total' => $item->total,
                ]);
            }

            // Update order status
            $order->update(['status' => 'confirmed']);

            return $invoice;
        });
    }
}
