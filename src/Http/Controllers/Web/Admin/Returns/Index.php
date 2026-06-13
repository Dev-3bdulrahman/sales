<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Returns;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Sales\Services\SalesReturnService;
use Dev3bdulrahman\Sales\Models\SalesReturn;
use Dev3bdulrahman\Sales\Models\Invoice;
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
    public ?int $salesReturnId = null;
    public ?int $customer_id = null;
    public ?int $invoice_id = null;
    public string $return_number = '';
    public string $return_date = '';
    public string $status = 'pending';
    public ?int $branch_id = null;
    public string $reason = '';

    // Items list
    public array $items = [];

    // Totals
    public float $subtotal = 0.0000;
    public float $tax_total = 0.0000;
    public float $discount_total = 0.0000;
    public float $grand_total = 0.0000;

    // Modals
    public bool $showFormModal = false;

    protected $listeners = ['delete' => 'deleteReturn'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        $this->return_date = now()->format('Y-m-d');
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
        $this->salesReturnId = null;
        $this->customer_id = null;
        $this->invoice_id = null;
        $this->return_number = 'SR-' . strtoupper(uniqid());
        $this->return_date = now()->format('Y-m-d');
        $this->status = 'pending';
        $this->branch_id = null;
        $this->reason = '';
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

    public function save(SalesReturnService $service)
    {
        $rules = [
            'customer_id' => 'required|exists:crm_customers,id',
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'return_number' => 'required|string|max:255',
            'return_date' => 'required|date',
            'status' => 'required|in:pending,approved,rejected,completed',
            'branch_id' => 'nullable|exists:branches,id',
            'reason' => 'nullable|string',
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
            'invoice_id' => $this->invoice_id,
            'return_number' => $this->return_number,
            'return_date' => $this->return_date,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'reason' => $this->reason,
        ];

        $service->createReturn($data, $this->items);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_created')]);

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteReturn(SalesReturnService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $service->deleteReturn($targetId);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_deleted')]);
        }
    }

    public function render(SalesReturnService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'customer_id' => $this->customerFilter,
        ];

        $returns = $service->listReturns($filters, 10);
        $customers = Customer::all();
        $invoices = Invoice::all();
        $products = Product::active()->get();
        $branches = Branch::all();

        return view('sales::livewire.admin.returns.index', [
            'returns' => $returns,
            'customers' => $customers,
            'invoices' => $invoices,
            'products' => $products,
            'branches' => $branches,
        ])->title(__('sales::sales.returns'));
    }
}
