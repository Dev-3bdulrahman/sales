<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sales Discounts (الخصومات)
        Schema::create('sales_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code'); // Coupon / Discount Code
            $table->string('type')->default('percentage'); // percentage, fixed
            $table->decimal('value', 15, 4);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('code');
            $table->index('is_active');
        });

        // 2. Sales Quotations (عروض الأسعار)
        Schema::create('sales_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('crm_customers')->onDelete('cascade');
            $table->string('quotation_number');
            $table->date('quotation_date');
            $table->date('expiry_date');
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_total', 15, 4)->default(0.0000);
            $table->decimal('discount_total', 15, 4)->default(0.0000);
            $table->decimal('grand_total', 15, 4)->default(0.0000);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('customer_id');
            $table->index('quotation_number');
            $table->index('status');
        });

        // 3. Sales Quotation Items
        Schema::create('sales_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('sales_quotations')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->timestamps();

            $table->index('quotation_id');
            $table->index('product_id');
        });

        // 4. Sales Orders (أوامر البيع)
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('crm_customers')->onDelete('cascade');
            $table->foreignId('quotation_id')->nullable()->constrained('sales_quotations')->onDelete('set null');
            $table->string('order_number');
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('draft'); // draft, pending, confirmed, processing, shipped, delivered, cancelled
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_total', 15, 4)->default(0.0000);
            $table->decimal('discount_total', 15, 4)->default(0.0000);
            $table->decimal('grand_total', 15, 4)->default(0.0000);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('customer_id');
            $table->index('order_number');
            $table->index('status');
        });

        // 5. Sales Order Items
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->timestamps();

            $table->index('sales_order_id');
            $table->index('product_id');
        });

        // 6. Sales Invoices (الفواتير)
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('crm_customers')->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->onDelete('set null');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('status')->default('draft'); // draft, unpaid, partially_paid, paid, overdue, cancelled
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_total', 15, 4)->default(0.0000);
            $table->decimal('discount_total', 15, 4)->default(0.0000);
            $table->decimal('grand_total', 15, 4)->default(0.0000);
            $table->decimal('paid_amount', 15, 4)->default(0.0000);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('customer_id');
            $table->index('invoice_number');
            $table->index('status');
        });

        // 7. Sales Invoice Items
        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('sales_invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('product_id');
        });

        // 8. Sales Payments (المدفوعات)
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('invoice_id')->constrained('sales_invoices')->onDelete('cascade');
            $table->string('payment_number');
            $table->date('payment_date');
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, card, check, online
            $table->decimal('amount', 15, 4);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('invoice_id');
            $table->index('payment_number');
        });

        // 9. Sales Returns (مرتجع مبيعات)
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('crm_customers')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('sales_invoices')->onDelete('set null');
            $table->string('return_number');
            $table->date('return_date');
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax_total', 15, 4)->default(0.0000);
            $table->decimal('discount_total', 15, 4)->default(0.0000);
            $table->decimal('grand_total', 15, 4)->default(0.0000);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('customer_id');
            $table->index('invoice_id');
            $table->index('return_number');
            $table->index('status');
        });

        // 10. Sales Return Items
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->timestamps();

            $table->index('sales_return_id');
            $table->index('product_id');
        });

        // 11. Sales Commissions (العمولات)
        Schema::create('sales_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('sales_invoices')->onDelete('set null');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->onDelete('set null');
            $table->decimal('amount', 15, 4);
            $table->decimal('rate', 5, 2)->default(0.00); // percentage rate
            $table->string('status')->default('pending'); // pending, paid, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('user_id');
            $table->index('invoice_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_commissions');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('sales_payments');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('sales_quotation_items');
        Schema::dropIfExists('sales_quotations');
        Schema::dropIfExists('sales_discounts');
    }
};
