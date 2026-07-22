<?php

use Illuminate\Support\Facades\Route;
use Dev3bdulrahman\Sales\Http\Controllers\Api\QuotationApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\SalesOrderApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\InvoiceApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\PaymentApiController;
use Dev3bdulrahman\Sales\Http\Controllers\Api\SalesReturnApiController;

Route::prefix('api/v1/sales')->middleware(['auth:sanctum', 'throttle:60,1', 'api.tenant'])->group(function () {
    // Quotations
    Route::get('quotations', [QuotationApiController::class, 'index'])->middleware('can:sales.quotations.view')->name('api.v1.sales.quotations.index');
    Route::post('quotations', [QuotationApiController::class, 'store'])->middleware('can:sales.quotations.create')->name('api.v1.sales.quotations.store');
    Route::get('quotations/{quotation}', [QuotationApiController::class, 'show'])->middleware('can:sales.quotations.view')->name('api.v1.sales.quotations.show');
    Route::put('quotations/{quotation}', [QuotationApiController::class, 'update'])->middleware('can:sales.quotations.edit')->name('api.v1.sales.quotations.update');
    Route::delete('quotations/{quotation}', [QuotationApiController::class, 'destroy'])->middleware('can:sales.quotations.delete')->name('api.v1.sales.quotations.destroy');
    Route::post('quotations/{quotation}/convert', [QuotationApiController::class, 'convertToOrder'])->middleware('can:sales.quotations.convert')->name('api.v1.sales.quotations.convert');

    // Sales Orders
    Route::get('orders', [SalesOrderApiController::class, 'index'])->middleware('can:sales.orders.view')->name('api.v1.sales.orders.index');
    Route::post('orders', [SalesOrderApiController::class, 'store'])->middleware('can:sales.orders.create')->name('api.v1.sales.orders.store');
    Route::get('orders/{salesOrder}', [SalesOrderApiController::class, 'show'])->middleware('can:sales.orders.view')->name('api.v1.sales.orders.show');
    Route::put('orders/{salesOrder}', [SalesOrderApiController::class, 'update'])->middleware('can:sales.orders.edit')->name('api.v1.sales.orders.update');
    Route::delete('orders/{salesOrder}', [SalesOrderApiController::class, 'destroy'])->middleware('can:sales.orders.delete')->name('api.v1.sales.orders.destroy');
    Route::post('orders/{salesOrder}/convert-to-invoice', [SalesOrderApiController::class, 'convertToInvoice'])->middleware('can:sales.orders.convert')->name('api.v1.sales.orders.convert-to-invoice');

    // Invoices
    Route::get('invoices', [InvoiceApiController::class, 'index'])->middleware('can:sales.invoices.view')->name('api.v1.sales.invoices.index');
    Route::post('invoices', [InvoiceApiController::class, 'store'])->middleware('can:sales.invoices.create')->name('api.v1.sales.invoices.store');
    Route::get('invoices/{invoice}', [InvoiceApiController::class, 'show'])->middleware('can:sales.invoices.view')->name('api.v1.sales.invoices.show');
    Route::put('invoices/{invoice}', [InvoiceApiController::class, 'update'])->middleware('can:sales.invoices.edit')->name('api.v1.sales.invoices.update');
    Route::delete('invoices/{invoice}', [InvoiceApiController::class, 'destroy'])->middleware('can:sales.invoices.delete')->name('api.v1.sales.invoices.destroy');

    // Payments
    Route::get('payments', [PaymentApiController::class, 'index'])->middleware('can:sales.payments.view')->name('api.v1.sales.payments.index');
    Route::post('payments', [PaymentApiController::class, 'store'])->middleware('can:sales.payments.create')->name('api.v1.sales.payments.store');
    Route::delete('payments/{payment}', [PaymentApiController::class, 'destroy'])->middleware('can:sales.payments.delete')->name('api.v1.sales.payments.destroy');

    // Returns
    Route::get('returns', [SalesReturnApiController::class, 'index'])->middleware('can:sales.returns.view')->name('api.v1.sales.returns.index');
    Route::post('returns', [SalesReturnApiController::class, 'store'])->middleware('can:sales.returns.create')->name('api.v1.sales.returns.store');
    Route::get('returns/{salesReturn}', [SalesReturnApiController::class, 'show'])->middleware('can:sales.returns.view')->name('api.v1.sales.returns.show');
    Route::put('returns/{salesReturn}', [SalesReturnApiController::class, 'update'])->middleware('can:sales.returns.edit')->name('api.v1.sales.returns.update');
    Route::delete('returns/{salesReturn}', [SalesReturnApiController::class, 'destroy'])->middleware('can:sales.returns.delete')->name('api.v1.sales.returns.destroy');
});
