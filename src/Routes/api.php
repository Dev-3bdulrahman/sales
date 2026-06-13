<?php

use Illuminate\Support\Facades\Route;
use Dev3bdulrahman\Sales\Http\Controllers\Api\QuotationApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\SalesOrderApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\InvoiceApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\PaymentApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\SalesReturnApiController;

Route::prefix('api/v1/sales')->middleware(['api', 'auth'])->group(function () {
    // Quotations
    Route::get('quotations', [QuotationApiController::class, 'index'])->middleware('can:sales.quotations.view');
    Route::post('quotations', [QuotationApiController::class, 'store'])->middleware('can:sales.quotations.create');
    Route::get('quotations/{id}', [QuotationApiController::class, 'show'])->middleware('can:sales.quotations.view');
    Route::put('quotations/{id}', [QuotationApiController::class, 'update'])->middleware('can:sales.quotations.edit');
    Route::delete('quotations/{id}', [QuotationApiController::class, 'destroy'])->middleware('can:sales.quotations.delete');
    Route::post('quotations/{id}/convert', [QuotationApiController::class, 'convertToOrder'])->middleware('can:sales.orders.create');

    // Sales Orders
    Route::get('orders', [SalesOrderApiController::class, 'index'])->middleware('can:sales.orders.view');
    Route::post('orders', [SalesOrderApiController::class, 'store'])->middleware('can:sales.orders.create');
    Route::get('orders/{id}', [SalesOrderApiController::class, 'show'])->middleware('can:sales.orders.view');
    Route::put('orders/{id}', [SalesOrderApiController::class, 'update'])->middleware('can:sales.orders.edit');
    Route::delete('orders/{id}', [SalesOrderApiController::class, 'destroy'])->middleware('can:sales.orders.delete');
    Route::post('orders/{id}/convert-to-invoice', [SalesOrderApiController::class, 'convertToInvoice'])->middleware('can:sales.invoices.create');

    // Invoices
    Route::get('invoices', [InvoiceApiController::class, 'index'])->middleware('can:sales.invoices.view');
    Route::post('invoices', [InvoiceApiController::class, 'store'])->middleware('can:sales.invoices.create');
    Route::get('invoices/{id}', [InvoiceApiController::class, 'show'])->middleware('can:sales.invoices.view');
    Route::put('invoices/{id}', [InvoiceApiController::class, 'update'])->middleware('can:sales.invoices.edit');
    Route::delete('invoices/{id}', [InvoiceApiController::class, 'destroy'])->middleware('can:sales.invoices.delete');

    // Payments
    Route::get('payments', [PaymentApiController::class, 'index'])->middleware('can:sales.payments.view');
    Route::post('payments', [PaymentApiController::class, 'store'])->middleware('can:sales.payments.create');
    Route::delete('payments/{id}', [PaymentApiController::class, 'destroy'])->middleware('can:sales.payments.delete');

    // Returns
    Route::get('returns', [SalesReturnApiController::class, 'index'])->middleware('can:sales.returns.view');
    Route::post('returns', [SalesReturnApiController::class, 'store'])->middleware('can:sales.returns.create');
    Route::get('returns/{id}', [SalesReturnApiController::class, 'show'])->middleware('can:sales.returns.view');
    Route::delete('returns/{id}', [SalesReturnApiController::class, 'destroy'])->middleware('can:sales.returns.delete');
});
