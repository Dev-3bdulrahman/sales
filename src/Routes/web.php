<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:super-admin|developer|admin|employee', 'license'])
    ->prefix('admin')
    ->group(function () {
        // Quotations
        Route::get('/sales/quotations', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Quotations\Index::class)->name('admin.sales.quotations');
        // Sales Orders
        Route::get('/sales/orders', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Orders\Index::class)->name('admin.sales.orders');
        // Invoices
        Route::get('/sales/invoices', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Invoices\Index::class)->name('admin.sales.invoices');
        // Returns
        Route::get('/sales/returns', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Returns\Index::class)->name('admin.sales.returns');
    });
