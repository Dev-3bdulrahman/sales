<?php

namespace Dev3bdulrahman\Sales\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSalesReturnApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:crm_customers,id',
            'invoice_id' => 'required|exists:sales_invoices,id',
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
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => __('sales::sales.validation_failed'),
                'data' => null,
                'meta' => [],
                'errors' => $validator->errors()->toArray(),
            ], 422)
        );
    }
}
