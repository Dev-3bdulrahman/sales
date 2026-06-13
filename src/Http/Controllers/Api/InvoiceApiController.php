<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Sales\Http\Resources\InvoiceResource;
use Dev3bdulrahman\Sales\Services\InvoiceService;
use Dev3bdulrahman\Sales\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceApiController extends Controller
{
    /**
     * List all invoices.
     */
    public function index(Request $request, InvoiceService $service): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $invoices = $service->listInvoices($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Invoices retrieved successfully'),
            'data' => InvoiceResource::collection($invoices->items()),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
            'errors' => []
        ]);
    }

    /**
     * Store a new invoice.
     */
    public function store(Request $request, InvoiceService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'status' => 'nullable|string|in:draft,unpaid,partially_paid,paid,overdue,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
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

        $invoice = $service->createInvoice($validated, $items);
        $invoice->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Invoice created successfully'),
            'data' => new InvoiceResource($invoice),
            'errors' => []
        ], 201);
    }

    /**
     * Show a single invoice.
     */
    public function show($id, InvoiceService $service): JsonResponse
    {
        $invoice = Invoice::with(['items', 'payments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Invoice details retrieved'),
            'data' => new InvoiceResource($invoice),
            'errors' => []
        ]);
    }

    /**
     * Update an existing invoice.
     */
    public function update($id, Request $request, InvoiceService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'status' => 'nullable|string|in:draft,unpaid,partially_paid,paid,overdue,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
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

        $invoice = $service->updateInvoice($id, $validated, $items);
        $invoice->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Invoice updated successfully'),
            'data' => new InvoiceResource($invoice),
            'errors' => []
        ]);
    }

    /**
     * Delete an invoice.
     */
    public function destroy($id, InvoiceService $service): JsonResponse
    {
        $service->deleteInvoice($id);

        return response()->json([
            'success' => true,
            'message' => __('Invoice deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
