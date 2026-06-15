<?php

use Illuminate\Support\Facades\Route;
use Dev3bdulrahman\Sales\Http\Controllers\Api\QuotationApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\SalesOrderApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\InvoiceApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\PaymentApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\SalesReturnApiController;

Route::prefix('api/v1/sales')->middleware(['auth:sanctum', 'throttle:60,1', 'api.tenant'])->group(function () {
    // Quotations
    Route::get('quotations', [QuotationApiController::class, 'index'])->name('api.v1.sales.quotations.index');
    Route::post('quotations', [QuotationApiController::class, 'store'])->name('api.v1.sales.quotations.store');
    Route::get('quotations/{quotation}', [QuotationApiController::class, 'show'])->name('api.v1.sales.quotations.show');
    Route::put('quotations/{quotation}', [QuotationApiController::class, 'update'])->name('api.v1.sales.quotations.update');
    Route::delete('quotations/{quotation}', [QuotationApiController::class, 'destroy'])->name('api.v1.sales.quotations.destroy');
    Route::post('quotations/{quotation}/convert', [QuotationApiController::class, 'convertToOrder'])->name('api.v1.sales.quotations.convert');

    // Sales Orders
    Route::get('orders', [SalesOrderApiController::class, 'index'])->name('api.v1.sales.orders.index');
    Route::post('orders', [SalesOrderApiController::class, 'store'])->name('api.v1.sales.orders.store');
    Route::get('orders/{salesOrder}', [SalesOrderApiController::class, 'show'])->name('api.v1.sales.orders.show');
    Route::put('orders/{salesOrder}', [SalesOrderApiController::class, 'update'])->name('api.v1.sales.orders.update');
    Route::delete('orders/{salesOrder}', [SalesOrderApiController::class, 'destroy'])->name('api.v1.sales.orders.destroy');
    Route::post('orders/{salesOrder}/convert-to-invoice', [SalesOrderApiController::class, 'convertToInvoice'])->name('api.v1.sales.orders.convert-to-invoice');

    // Invoices
    Route::get('invoices', [InvoiceApiController::class, 'index'])->name('api.v1.sales.invoices.index');
    Route::post('invoices', [InvoiceApiController::class, 'store'])->name('api.v1.sales.invoices.store');
    Route::get('invoices/{invoice}', [InvoiceApiController::class, 'show'])->name('api.v1.sales.invoices.show');
    Route::put('invoices/{invoice}', [InvoiceApiController::class, 'update'])->name('api.v1.sales.invoices.update');
    Route::delete('invoices/{invoice}', [InvoiceApiController::class, 'destroy'])->name('api.v1.sales.invoices.destroy');

    // Payments
    Route::get('payments', [PaymentApiController::class, 'index'])->name('api.v1.sales.payments.index');
    Route::post('payments', [PaymentApiController::class, 'store'])->name('api.v1.sales.payments.store');
    Route::delete('payments/{payment}', [PaymentApiController::class, 'destroy'])->name('api.v1.sales.payments.destroy');

    // Returns
    Route::get('returns', [SalesReturnApiController::class, 'index'])->name('api.v1.sales.returns.index');
    Route::post('returns', [SalesReturnApiController::class, 'store'])->name('api.v1.sales.returns.store');
    Route::get('returns/{salesReturn}', [SalesReturnApiController::class, 'show'])->name('api.v1.sales.returns.show');
    Route::put('returns/{salesReturn}', [SalesReturnApiController::class, 'update'])->name('api.v1.sales.returns.update');
    Route::delete('returns/{salesReturn}', [SalesReturnApiController::class, 'destroy'])->name('api.v1.sales.returns.destroy');
});
