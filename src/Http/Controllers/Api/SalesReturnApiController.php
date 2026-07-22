<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Sales\Events\SalesReturnCreated;
use Dev3bdulrahman\Sales\Http\Requests\Api\StoreSalesReturnApiRequest;
use Dev3bdulrahman\Sales\Http\Requests\Api\UpdateSalesReturnApiRequest;
use Dev3bdulrahman\Sales\Http\Resources\SalesReturnResource;
use Dev3bdulrahman\Sales\Services\SalesReturnService;
use Dev3bdulrahman\Sales\Models\SalesReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesReturnApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all returns.
     */
    public function index(Request $request, SalesReturnService $service): JsonResponse
    {
        $this->authorize('viewAny', SalesReturn::class);

        $filters = $request->only(['search', 'status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 10);
        $returns = $service->listReturns($filters, $perPage);

        return $this->success(
            SalesReturnResource::collection($returns->items()),
            __('Sales Returns retrieved successfully'),
            200,
            [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ]
        );
    }

    /**
     * Store a new return.
     */
    public function store(StoreSalesReturnApiRequest $request, SalesReturnService $service): JsonResponse
    {
        $this->authorize('create', SalesReturn::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $return = $service->createReturn($validated, $items);
        $return->load('items');

        SalesReturnCreated::dispatch($return, auth()->id(), auth()->user()->company_id);

        return $this->success(
            new SalesReturnResource($return),
            __('Sales Return created successfully'),
            201
        );
    }

    /**
     * Show a single return.
     */
    public function show(SalesReturn $salesReturn): JsonResponse
    {
        $this->authorize('view', $salesReturn);

        $salesReturn->load('items');

        return $this->success(
            new SalesReturnResource($salesReturn),
            __('Sales Return details retrieved')
        );
    }

    /**
     * Update an existing return.
     */
    public function update(UpdateSalesReturnApiRequest $request, SalesReturn $salesReturn, SalesReturnService $service): JsonResponse
    {
        $this->authorize('update', $salesReturn);

        $validated = $request->validated();
        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $return = $service->updateReturn($salesReturn->id, $validated, $items ?? []);
        $return->load('items');

        return $this->success(
            new SalesReturnResource($return),
            __('Sales Return updated successfully')
        );
    }

    /**
     * Delete a return.
     */
    public function destroy(SalesReturn $salesReturn, SalesReturnService $service): JsonResponse
    {
        $this->authorize('delete', $salesReturn);

        $service->deleteReturn($salesReturn->id);

        return $this->success(
            null,
            __('Sales Return deleted successfully')
        );
    }
}
