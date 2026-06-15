<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Sales\Http\Requests\Api\StoreQuotationApiRequest;
use Dev3bdulrahman\Sales\Http\Requests\Api\UpdateQuotationApiRequest;
use Dev3bdulrahman\Sales\Http\Resources\QuotationResource;
use Dev3bdulrahman\Sales\Services\QuotationService;
use Dev3bdulrahman\Sales\Models\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all quotations.
     */
    public function index(Request $request, QuotationService $service): JsonResponse
    {
        $this->authorize('viewAny', Quotation::class);

        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $quotations = $service->listQuotations($filters, $perPage);

        return $this->success(
            QuotationResource::collection($quotations->items()),
            __('Quotations retrieved successfully'),
            200,
            [
                'current_page' => $quotations->currentPage(),
                'last_page' => $quotations->lastPage(),
                'per_page' => $quotations->perPage(),
                'total' => $quotations->total(),
            ]
        );
    }

    /**
     * Store a new quotation.
     */
    public function store(StoreQuotationApiRequest $request, QuotationService $service): JsonResponse
    {
        $this->authorize('create', Quotation::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $quotation = $service->createQuotation($validated, $items);
        $quotation->load('items');

        return $this->success(
            new QuotationResource($quotation),
            __('Quotation created successfully'),
            201
        );
    }

    /**
     * Show a single quotation.
     */
    public function show(Quotation $quotation): JsonResponse
    {
        $this->authorize('view', $quotation);

        $quotation->load('items');

        return $this->success(
            new QuotationResource($quotation),
            __('Quotation details retrieved')
        );
    }

    /**
     * Update an existing quotation.
     */
    public function update(UpdateQuotationApiRequest $request, Quotation $quotation, QuotationService $service): JsonResponse
    {
        $this->authorize('update', $quotation);

        $validated = $request->validated();
        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $quotation = $service->updateQuotation($quotation->id, $validated, $items ?? []);
        $quotation->load('items');

        return $this->success(
            new QuotationResource($quotation),
            __('Quotation updated successfully')
        );
    }

    /**
     * Delete a quotation.
     */
    public function destroy(Quotation $quotation, QuotationService $service): JsonResponse
    {
        $this->authorize('delete', $quotation);

        $service->deleteQuotation($quotation->id);

        return $this->success(
            null,
            __('Quotation deleted successfully')
        );
    }

    /**
     * Convert Quotation to Order.
     */
    public function convertToOrder(Quotation $quotation, Request $request, QuotationService $service): JsonResponse
    {
        $this->authorize('create', \Dev3bdulrahman\Sales\Models\SalesOrder::class);

        $validated = $request->validate([
            'order_number' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $order = $service->convertToOrder($quotation->id, $validated);

        return $this->success(
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            __('Quotation converted to Order successfully')
        );
    }
}
