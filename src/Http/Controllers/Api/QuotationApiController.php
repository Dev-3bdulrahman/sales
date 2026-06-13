<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Sales\Http\Resources\QuotationResource;
use Dev3bdulrahman\Sales\Services\QuotationService;
use Dev3bdulrahman\Sales\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuotationApiController extends Controller
{
    /**
     * List all quotations.
     */
    public function index(Request $request, QuotationService $service): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $quotations = $service->listQuotations($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Quotations retrieved successfully'),
            'data' => QuotationResource::collection($quotations->items()),
            'meta' => [
                'current_page' => $quotations->currentPage(),
                'last_page' => $quotations->lastPage(),
                'per_page' => $quotations->perPage(),
                'total' => $quotations->total(),
            ],
            'errors' => []
        ]);
    }

    /**
     * Store a new quotation.
     */
    public function store(Request $request, QuotationService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'quotation_number' => 'required|string|max:255',
            'quotation_date' => 'required|date',
            'expiry_date' => 'required|date',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected,expired',
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

        $quotation = $service->createQuotation($validated, $items);
        $quotation->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Quotation created successfully'),
            'data' => new QuotationResource($quotation),
            'errors' => []
        ], 201);
    }

    /**
     * Show a single quotation.
     */
    public function show($id, QuotationService $service): JsonResponse
    {
        $quotation = Quotation::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Quotation details retrieved'),
            'data' => new QuotationResource($quotation),
            'errors' => []
        ]);
    }

    /**
     * Update an existing quotation.
     */
    public function update($id, Request $request, QuotationService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'quotation_number' => 'required|string|max:255',
            'quotation_date' => 'required|date',
            'expiry_date' => 'required|date',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected,expired',
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

        $quotation = $service->updateQuotation($id, $validated, $items);
        $quotation->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Quotation updated successfully'),
            'data' => new QuotationResource($quotation),
            'errors' => []
        ]);
    }

    /**
     * Delete a quotation.
     */
    public function destroy($id, QuotationService $service): JsonResponse
    {
        $service->deleteQuotation($id);

        return response()->json([
            'success' => true,
            'message' => __('Quotation deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }

    /**
     * Convert Quotation to Order.
     */
    public function convertToOrder($id, Request $request, QuotationService $service): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $order = $service->convertToOrder($id, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Quotation converted to Order successfully'),
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            'errors' => []
        ]);
    }
}
