<?php

namespace Dev3bdulrahman\Sales;

use Illuminate\Support\ServiceProvider;

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

        // Register Livewire Components
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('sales-quotations-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Quotations\Index::class);
            \Livewire\Livewire::component('sales-orders-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Orders\Index::class);
            \Livewire\Livewire::component('sales-invoices-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Invoices\Index::class);
            \Livewire\Livewire::component('sales-returns-index', \Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Returns\Index::class);
        }
    }
}
