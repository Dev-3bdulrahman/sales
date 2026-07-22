<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Sales\Http\Requests\Api\StoreSalesOrderApiRequest;
use Dev3bdulrahman\Sales\Http\Requests\Api\UpdateSalesOrderApiRequest;
use Dev3bdulrahman\Sales\Http\Resources\SalesOrderResource;
use Dev3bdulrahman\Sales\Services\SalesOrderService;
use Dev3bdulrahman\Sales\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrderApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all orders.
     */
    public function index(Request $request, SalesOrderService $service): JsonResponse
    {
        $this->authorize('viewAny', SalesOrder::class);

        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $orders = $service->listOrders($filters, $perPage);

        return $this->success(
            SalesOrderResource::collection($orders->items()),
            __('Sales Orders retrieved successfully'),
            200,
            [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        );
    }

    /**
     * Store a new sales order.
     */
    public function store(StoreSalesOrderApiRequest $request, SalesOrderService $service): JsonResponse
    {
        $this->authorize('create', SalesOrder::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $order = $service->createOrder($validated, $items);
        $order->load('items');

        return $this->success(
            new SalesOrderResource($order),
            __('Sales Order created successfully'),
            201
        );
    }

    /**
     * Show a single order.
     */
    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load('items');

        return $this->success(
            new SalesOrderResource($salesOrder),
            __('Sales Order details retrieved')
        );
    }

    /**
     * Update an existing order.
     */
    public function update(UpdateSalesOrderApiRequest $request, SalesOrder $salesOrder, SalesOrderService $service): JsonResponse
    {
        $this->authorize('update', $salesOrder);

        $validated = $request->validated();
        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $order = $service->updateOrder($salesOrder->id, $validated, $items ?? []);
        $order->load('items');

        return $this->success(
            new SalesOrderResource($order),
            __('Sales Order updated successfully')
        );
    }

    /**
     * Delete an order.
     */
    public function destroy(SalesOrder $salesOrder, SalesOrderService $service): JsonResponse
    {
        $this->authorize('delete', $salesOrder);

        $service->deleteOrder($salesOrder->id);

        return $this->success(
            null,
            __('Sales Order deleted successfully')
        );
    }

    /**
     * Convert Order to Invoice.
     */
    public function convertToInvoice(SalesOrder $salesOrder, Request $request, SalesOrderService $service): JsonResponse
    {
        $this->authorize('create', \Dev3bdulrahman\Sales\Models\Invoice::class);

        $validated = $request->validate([
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = $service->convertToInvoice($salesOrder->id, $validated);

        return $this->success(
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
            __('Sales Order converted to Invoice successfully')
        );
    }
}
