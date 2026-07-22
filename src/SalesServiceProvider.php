<?php

namespace Dev3bdulrahman\Sales;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Dev3bdulrahman\Sales\Events\InvoiceIssued;
use Dev3bdulrahman\Sales\Events\PaymentReceived;
use Dev3bdulrahman\Sales\Events\QuotationApproved;
use Dev3bdulrahman\Sales\Listeners\LogInvoiceIssued;
use Dev3bdulrahman\Sales\Listeners\LogQuotationApproval;
use Dev3bdulrahman\Sales\Listeners\UpdateInvoiceOnPayment;
use Dev3bdulrahman\Sales\Models\Invoice;
use Dev3bdulrahman\Sales\Models\Payment;
use Dev3bdulrahman\Sales\Models\Quotation;
use Dev3bdulrahman\Sales\Models\SalesOrder;
use Dev3bdulrahman\Sales\Models\SalesReturn;
use Dev3bdulrahman\Sales\Policies\InvoicePolicy;
use Dev3bdulrahman\Sales\Policies\PaymentPolicy;
use Dev3bdulrahman\Sales\Policies\QuotationPolicy;
use Dev3bdulrahman\Sales\Policies\SalesOrderPolicy;
use Dev3bdulrahman\Sales\Policies\SalesReturnPolicy;

class SalesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/Views', 'sales');

        // Load package translations
        $this->loadTranslationsFrom(__DIR__ . '/Translations', 'sales');

        // Register Policies
        Gate::policy(Quotation::class, QuotationPolicy::class);
        Gate::policy(SalesOrder::class, SalesOrderPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(SalesReturn::class, SalesReturnPolicy::class);

        // Register Event Listeners
        Event::listen(QuotationApproved::class, LogQuotationApproval::class);
        Event::listen(InvoiceIssued::class, LogInvoiceIssued::class);
        Event::listen(PaymentReceived::class, UpdateInvoiceOnPayment::class);

        // Register Livewire Components
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('sales-quotations-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Quotations\Index::class);
            \Livewire\Livewire::component('sales-orders-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Orders\Index::class);
            \Livewire\Livewire::component('sales-invoices-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Invoices\Index::class);
            \Livewire\Livewire::component('sales-returns-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Returns\Index::class);
        }
    }
}
