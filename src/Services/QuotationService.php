<?php

namespace Dev3bdulrahman\Sales\Services;

use Dev3bdulrahman\Sales\Models\Quotation;
use Dev3bdulrahman\Sales\Models\QuotationItem;
use Dev3bdulrahman\Sales\Models\SalesOrder;
use Dev3bdulrahman\Sales\Models\SalesOrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    /**
     * List quotations with search and filters.
     */
    public function listQuotations(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Quotation::query()->with(['customer', 'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
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
     * Create a new Quotation.
     */
    public function createQuotation(array $data, array $items = []): Quotation
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0.0000;
            $taxTotal = 0.0000;
            $discountTotal = 0.0000;

            // Calculate totals
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

            $quotation = Quotation::create($data);

            foreach ($itemsToCreate as $itemData) {
                $quotation->items()->create($itemData);
            }

            return $quotation;
        });
    }

    /**
     * Update an existing Quotation.
     */
    public function updateQuotation($id, array $data, array $items = []): Quotation
    {
        return DB::transaction(function () use ($id, $data, $items) {
            $quotation = Quotation::findOrFail($id);

            $subtotal = 0.0000;
            $taxTotal = 0.0000;
            $discountTotal = 0.0000;

            // Delete old items
            $quotation->items()->delete();

            // Calculate and create new items
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

                $quotation->items()->create([
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

            $quotation->update($data);

            return $quotation;
        });
    }

    /**
     * Delete a Quotation.
     */
    public function deleteQuotation($id): bool
    {
        $quotation = Quotation::findOrFail($id);
        return $quotation->delete();
    }

    /**
     * Convert Quotation to a Sales Order.
     */
    public function convertToOrder($id, array $additionalData = []): SalesOrder
    {
        return DB::transaction(function () use ($id, $additionalData) {
            $quotation = Quotation::with('items')->findOrFail($id);

            // Create Sales Order
            $orderNumber = $additionalData['order_number'] ?? 'SO-' . strtoupper(uniqid());
            $orderDate = $additionalData['order_date'] ?? now()->format('Y-m-d');

            $order = SalesOrder::create([
                'company_id' => $quotation->company_id,
                'branch_id' => $quotation->branch_id,
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'order_number' => $orderNumber,
                'order_date' => $orderDate,
                'delivery_date' => $additionalData['delivery_date'] ?? null,
                'status' => 'pending',
                'subtotal' => $quotation->subtotal,
                'tax_total' => $quotation->tax_total,
                'discount_total' => $quotation->discount_total,
                'grand_total' => $quotation->grand_total,
                'notes' => $additionalData['notes'] ?? $quotation->notes,
                'created_by' => auth()->id() ?? $quotation->created_by,
            ]);

            // Copy items
            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
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

            // Update Quotation status
            $quotation->update(['status' => 'accepted']);

            return $order;
        });
    }
}
