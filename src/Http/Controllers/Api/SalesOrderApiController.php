<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Sales\Http\Resources\SalesOrderResource;
use Dev3bdulrahman\Sales\Services\SalesOrderService;
use Dev3bdulrahman\Sales\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesOrderApiController extends Controller
{
    /**
     * List all orders.
     */
    public function index(Request $request, SalesOrderService $service): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $orders = $service->listOrders($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Sales Orders retrieved successfully'),
            'data' => SalesOrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'errors' => []
        ]);
    }

    /**
     * Store a new sales order.
     */
    public function store(Request $request, SalesOrderService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'quotation_id' => 'nullable|exists:sales_quotations,id',
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,pending,confirmed,processing,shipped,delivered,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $order = $service->createOrder($validated, $items);
        $order->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Sales Order created successfully'),
            'data' => new SalesOrderResource($order),
            'errors' => []
        ], 201);
    }

    /**
     * Show a single order.
     */
    public function show($id, SalesOrderService $service): JsonResponse
    {
        $order = SalesOrder::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Sales Order details retrieved'),
            'data' => new SalesOrderResource($order),
            'errors' => []
        ]);
    }

    /**
     * Update an existing order.
     */
    public function update($id, Request $request, SalesOrderService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'quotation_id' => 'nullable|exists:sales_quotations,id',
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,pending,confirmed,processing,shipped,delivered,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $order = $service->updateOrder($id, $validated, $items);
        $order->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Sales Order updated successfully'),
            'data' => new SalesOrderResource($order),
            'errors' => []
        ]);
    }

    /**
     * Delete an order.
     */
    public function destroy($id, SalesOrderService $service): JsonResponse
    {
        $service->deleteOrder($id);

        return response()->json([
            'success' => true,
            'message' => __('Sales Order deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }

    /**
     * Convert Order to Invoice.
     */
    public function convertToInvoice($id, Request $request, SalesOrderService $service): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = $service->convertToInvoice($id, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Sales Order converted to Invoice successfully'),
            'data' => [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
            'errors' => []
        ]);
    }
}
