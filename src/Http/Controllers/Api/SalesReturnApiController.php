<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Sales\Http\Resources\SalesReturnResource;
use Dev3bdulrahman\Sales\Services\SalesReturnService;
use Dev3bdulrahman\Sales\Models\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesReturnApiController extends Controller
{
    /**
     * List all returns.
     */
    public function index(Request $request, SalesReturnService $service): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $returns = $service->listReturns($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Sales Returns retrieved successfully'),
            'data' => SalesReturnResource::collection($returns->items()),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ],
            'errors' => []
        ]);
    }

    /**
     * Store a new return.
     */
    public function store(Request $request, SalesReturnService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'return_number' => 'required|string|max:255',
            'return_date' => 'required|date',
            'status' => 'nullable|string|in:pending,approved,rejected,completed',
            'branch_id' => 'nullable|exists:branches,id',
            'reason' => 'nullable|string',
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

        $return = $service->createReturn($validated, $items);
        $return->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Sales Return created successfully'),
            'data' => new SalesReturnResource($return),
            'errors' => []
        ], 201);
    }

    /**
     * Show a single return.
     */
    public function show($id, SalesReturnService $service): JsonResponse
    {
        $return = SalesReturn::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Sales Return details retrieved'),
            'data' => new SalesReturnResource($return),
            'errors' => []
        ]);
    }

    /**
     * Delete a return.
     */
    public function destroy($id, SalesReturnService $service): JsonResponse
    {
        $service->deleteReturn($id);

        return response()->json([
            'success' => true,
            'message' => __('Sales Return deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
