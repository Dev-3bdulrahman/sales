<?php

namespace Dev3bdulrahman\Sales\Services;

use Dev3bdulrahman\Sales\Models\SalesReturn;
use Dev3bdulrahman\Sales\Models\SalesReturnItem;
use Dev3bdulrahman\Sales\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesReturnService
{
    /**
     * List returns with search and filters.
     */
    public function listReturns(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SalesReturn::query()->with(['customer', 'creator', 'invoice']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
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
     * Create a new Return.
     */
    public function createReturn(array $data, array $items = []): SalesReturn
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

            $return = SalesReturn::create($data);

            foreach ($itemsToCreate as $itemData) {
                $return->items()->create($itemData);
            }

            // Optionally adjust invoice or fire event
            return $return;
        });
    }

    /**
     * Delete a Return.
     */
    public function deleteReturn($id): bool
    {
        $return = SalesReturn::findOrFail($id);
        return $return->delete();
    }
}
