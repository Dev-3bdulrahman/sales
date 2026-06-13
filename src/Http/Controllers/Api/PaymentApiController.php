<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Sales\Http\Resources\PaymentResource;
use Dev3bdulrahman\Sales\Services\InvoiceService;
use Dev3bdulrahman\Sales\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentApiController extends Controller
{
    /**
     * List all payments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query()->with('invoice');
        
        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        $perPage = (int) $request->get('per_page', 10);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => __('Payments retrieved successfully'),
            'data' => PaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
            'errors' => []
        ]);
    }

    /**
     * Record a new payment.
     */
    public function store(Request $request, InvoiceService $service): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:sales_invoices,id',
            'payment_number' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,card,check,online',
            'amount' => 'required|numeric|min:0.0001',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoiceId = $validated['invoice_id'];
        unset($validated['invoice_id']);

        $payment = $service->recordPayment($invoiceId, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Payment recorded successfully'),
            'data' => new PaymentResource($payment),
            'errors' => []
        ], 201);
    }

    /**
     * Delete a payment.
     */
    public function destroy($id): JsonResponse
    {
        $payment = Payment::findOrFail($id);
        
        // Deduct paid_amount from invoice
        $invoice = $payment->invoice;
        if ($invoice) {
            $newPaidAmount = max(0, $invoice->paid_amount - $payment->amount);
            $status = 'unpaid';
            if ($newPaidAmount >= $invoice->grand_total) {
                $status = 'paid';
            } elseif ($newPaidAmount > 0) {
                $status = 'partially_paid';
            }
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
            ]);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => __('Payment deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
