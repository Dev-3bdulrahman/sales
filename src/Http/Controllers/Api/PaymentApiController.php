<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Sales\Events\PaymentReceived;
use Dev3bdulrahman\Sales\Http\Requests\Api\StorePaymentApiRequest;
use Dev3bdulrahman\Sales\Http\Resources\PaymentResource;
use Dev3bdulrahman\Sales\Models\Invoice;
use Dev3bdulrahman\Sales\Models\Payment;
use Dev3bdulrahman\Sales\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all payments.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::query()->with('invoice');

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        $perPage = (int) $request->get('per_page', 10);
        $payments = $query->paginate($perPage);

        return $this->success(
            PaymentResource::collection($payments->items()),
            __('Payments retrieved successfully'),
            200,
            [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        );
    }

    /**
     * Record a new payment.
     */
    public function store(StorePaymentApiRequest $request, InvoiceService $service): JsonResponse
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validated();
        $invoiceId = $validated['invoice_id'];
        unset($validated['invoice_id']);

        $payment = $service->recordPayment($invoiceId, $validated);
        $invoice = Invoice::findOrFail($invoiceId);

        PaymentReceived::dispatch($payment, $invoice, auth()->id(), auth()->user()->company_id);

        return $this->success(
            new PaymentResource($payment),
            __('Payment recorded successfully'),
            201
        );
    }

    /**
     * Delete a payment.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);

        // Deduct paid_amount from invoice
        $invoice = $payment->invoice;
        $payment->delete();

        if ($invoice) {
            $newPaidAmount = $invoice->payments()->sum('amount');
            $status = $invoice->status;
            if ($newPaidAmount >= $invoice->grand_total) {
                $status = 'paid';
            } elseif ($newPaidAmount > 0) {
                $status = 'partial';
            } else {
                $status = 'unpaid';
            }
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
            ]);
        }

        return $this->success(
            null,
            __('Payment deleted successfully')
        );
    }
}
