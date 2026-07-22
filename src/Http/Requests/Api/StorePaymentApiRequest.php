<?php

namespace Dev3bdulrahman\Sales\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePaymentApiRequest extends FormRequest
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
            'invoice_id' => 'required|exists:sales_invoices,id',
            'payment_number' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
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
