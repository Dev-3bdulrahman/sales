<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Orders;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Sales\Services\SalesOrderService;
use Dev3bdulrahman\Sales\Models\SalesOrder;
use Dev3bdulrahman\Crm\Models\Customer;
use App\Models\Product;
use App\Models\Branch;

class Index extends Component
{
    use WithPagination;

    // Filters
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'customer')]
    public string $customerFilter = '';

    // Form fields
    public ?int $salesOrderId = null;
    public ?int $customer_id = null;
    public ?int $quotation_id = null;
    public string $order_number = '';
    public string $order_date = '';
    public string $delivery_date = '';
    public string $status = 'draft';
    public ?int $branch_id = null;
    public string $notes = '';

    // Items list
    public array $items = [];

    // Totals
    public float $subtotal = 0.0000;
    public float $tax_total = 0.0000;
    public float $discount_total = 0.0000;
    public float $grand_total = 0.0000;

    // Modals
    public bool $showFormModal = false;
    public bool $showConvertModal = false;

    // Conversion fields
    public string $invoice_number = '';
    public string $invoice_date = '';
    public string $due_date = '';
    public string $conversion_notes = '';

    protected $listeners = ['delete' => 'deleteOrder'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        $this->order_date = now()->format('Y-m-d');
        $this->delivery_date = now()->addDays(7)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCustomerFilter()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->salesOrderId = null;
        $this->customer_id = null;
        $this->quotation_id = null;
        $this->order_number = 'SO-' . strtoupper(uniqid());
        $this->order_date = now()->format('Y-m-d');
        $this->delivery_date = now()->addDays(7)->format('Y-m-d');
        $this->status = 'draft';
        $this->branch_id = null;
        $this->notes = '';
        $this->items = [];
        $this->subtotal = 0.00;
        $this->tax_total = 0.00;
        $this->discount_total = 0.00;
        $this->grand_total = 0.00;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->addItem();
        $this->showFormModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $order = SalesOrder::with('items')->findOrFail($id);

        $this->salesOrderId = $order->id;
        $this->customer_id = $order->customer_id;
        $this->quotation_id = $order->quotation_id;
        $this->order_number = $order->order_number;
        $this->order_date = $order->order_date->format('Y-m-d');
        $this->delivery_date = $order->delivery_date ? $order->delivery_date->format('Y-m-d') : '';
        $this->status = $order->status;
        $this->branch_id = $order->branch_id;
        $this->notes = $order->notes ?? '';

        foreach ($order->items as $item) {
            $this->items[] = [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'tax_rate' => (float) $item->tax_rate,
                'discount_amount' => (float) $item->discount_amount,
                'total' => (float) $item->total,
            ];
        }

        $this->recalculateTotals();
        $this->showFormModal = true;
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => null,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => 0.00,
            'tax_rate' => 15.00,
            'discount_amount' => 0.00,
            'total' => 0.00,
        ];
        $this->recalculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalculateTotals();
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$parts[0]]['unit_price'] = (float) $product->sale_price;
            }
        }

        $this->recalculateTotals();
    }

    public function recalculateTotals()
    {
        $this->subtotal = 0.00;
        $this->tax_total = 0.00;
        $this->discount_total = 0.00;

        foreach ($this->items as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);

            $itemSub = $qty * $price;
            $itemTax = ($itemSub - $discount) * ($taxRate / 100);
            $itemTotal = $itemSub - $discount + $itemTax;

            $this->items[$index]['total'] = $itemTotal;

            $this->subtotal += $itemSub;
            $this->discount_total += $discount;
            $this->tax_total += $itemTax;
        }

        $this->grand_total = $this->subtotal - $this->discount_total + $this->tax_total;
    }

    public function save(SalesOrderService $service)
    {
        $rules = [
            'customer_id' => 'required|exists:crm_customers,id',
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'status' => 'required|in:draft,pending,confirmed,processing,shipped,delivered,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ];

        $validated = $this->validate($rules);

        $data = [
            'customer_id' => $this->customer_id,
            'quotation_id' => $this->quotation_id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date ?: null,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'notes' => $this->notes,
        ];

        if ($this->salesOrderId) {
            $service->updateOrder($this->salesOrderId, $data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_updated')]);
        } else {
            $service->createOrder($data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_created')]);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteOrder(SalesOrderService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $service->deleteOrder($targetId);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_deleted')]);
        }
    }

    public function openConvertModal($id)
    {
        $order = SalesOrder::findOrFail($id);
        $this->salesOrderId = $order->id;
        $this->invoice_number = 'INV-' . strtoupper(uniqid());
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(15)->format('Y-m-d');
        $this->conversion_notes = $order->notes ?? '';
        $this->showConvertModal = true;
    }

    public function convert(SalesOrderService $service)
    {
        $rules = [
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'conversion_notes' => 'nullable|string',
        ];

        $this->validate($rules);

        $service->convertToInvoice($this->salesOrderId, [
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'notes' => $this->conversion_notes,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.convert_to_invoice') . ' ' . __('sales::sales.success_created')]);
        $this->showConvertModal = false;
    }

    public function render(SalesOrderService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'customer_id' => $this->customerFilter,
        ];

        $orders = $service->listOrders($filters, 10);
        $customers = Customer::all();
        $products = Product::active()->get();
        $branches = Branch::all();

        return view('sales::livewire.admin.orders.index', [
            'orders' => $orders,
            'customers' => $customers,
            'products' => $products,
            'branches' => $branches,
        ])->title(__('sales::sales.orders'));
    }
}
