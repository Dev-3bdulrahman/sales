<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Sales\Http\Requests\Api\StoreInvoiceApiRequest;
use Dev3bdulrahman\Sales\Http\Requests\Api\UpdateInvoiceApiRequest;
use Dev3bdulrahman\Sales\Http\Resources\InvoiceResource;
use Dev3bdulrahman\Sales\Services\InvoiceService;
use Dev3bdulrahman\Sales\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all invoices.
     */
    public function index(Request $request, InvoiceService $service): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $invoices = $service->listInvoices($filters, $perPage);

        return $this->success(
            InvoiceResource::collection($invoices->items()),
            __('Invoices retrieved successfully'),
            200,
            [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        );
    }

    /**
     * Store a new invoice.
     */
    public function store(StoreInvoiceApiRequest $request, InvoiceService $service): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $invoice = $service->createInvoice($validated, $items);
        $invoice->load('items');

        return $this->success(
            new InvoiceResource($invoice),
            __('Invoice created successfully'),
            201
        );
    }

    /**
     * Show a single invoice.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items', 'payments']);

        return $this->success(
            new InvoiceResource($invoice),
            __('Invoice details retrieved')
        );
    }

    /**
     * Update an existing invoice.
     */
    public function update(UpdateInvoiceApiRequest $request, Invoice $invoice, InvoiceService $service): JsonResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validated();
        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $invoice = $service->updateInvoice($invoice->id, $validated, $items ?? []);
        $invoice->load('items');

        return $this->success(
            new InvoiceResource($invoice),
            __('Invoice updated successfully')
        );
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Invoice $invoice, InvoiceService $service): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $service->deleteInvoice($invoice->id);

        return $this->success(
            null,
            __('Invoice deleted successfully')
        );
    }
}
