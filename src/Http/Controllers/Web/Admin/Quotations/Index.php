<?php

namespace Dev3bdulrahman\Sales\Http\Controllers\Web\Admin\Quotations;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Sales\Services\QuotationService;
use Dev3bdulrahman\Sales\Models\Quotation;
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
    public ?int $quotationId = null;
    public ?int $customer_id = null;
    public string $quotation_number = '';
    public string $quotation_date = '';
    public string $expiry_date = '';
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
    public string $order_number = '';
    public string $order_date = '';
    public string $delivery_date = '';
    public string $conversion_notes = '';

    protected $listeners = ['delete' => 'deleteQuotation'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        $this->quotation_date = now()->format('Y-m-d');
        $this->expiry_date = now()->addDays(15)->format('Y-m-d');
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
        $this->quotationId = null;
        $this->customer_id = null;
        $this->quotation_number = 'QT-' . strtoupper(uniqid());
        $this->quotation_date = now()->format('Y-m-d');
        $this->expiry_date = now()->addDays(15)->format('Y-m-d');
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
        $quotation = Quotation::with('items')->findOrFail($id);

        $this->quotationId = $quotation->id;
        $this->customer_id = $quotation->customer_id;
        $this->quotation_number = $quotation->quotation_number;
        $this->quotation_date = $quotation->quotation_date->format('Y-m-d');
        $this->expiry_date = $quotation->expiry_date->format('Y-m-d');
        $this->status = $quotation->status;
        $this->branch_id = $quotation->branch_id;
        $this->notes = $quotation->notes ?? '';

        foreach ($quotation->items as $item) {
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
            'tax_rate' => 15.00, // standard tax rate default
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
        // Check if product_id changed to populate default sale price
        // $key format: index.field (e.g. "0.product_id")
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

    public function save(QuotationService $service)
    {
        $rules = [
            'customer_id' => 'required|exists:crm_customers,id',
            'quotation_number' => 'required|string|max:255',
            'quotation_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:quotation_date',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
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
            'quotation_number' => $this->quotation_number,
            'quotation_date' => $this->quotation_date,
            'expiry_date' => $this->expiry_date,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'notes' => $this->notes,
        ];

        if ($this->quotationId) {
            $service->updateQuotation($this->quotationId, $data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_updated')]);
        } else {
            $service->createQuotation($data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_created')]);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteQuotation(QuotationService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $service->deleteQuotation($targetId);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.success_deleted')]);
        }
    }

    public function openConvertModal($id)
    {
        $quotation = Quotation::findOrFail($id);
        $this->quotationId = $quotation->id;
        $this->order_number = 'SO-' . strtoupper(uniqid());
        $this->order_date = now()->format('Y-m-d');
        $this->delivery_date = now()->addDays(7)->format('Y-m-d');
        $this->conversion_notes = $quotation->notes ?? '';
        $this->showConvertModal = true;
    }

    public function convert(QuotationService $service)
    {
        $rules = [
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'conversion_notes' => 'nullable|string',
        ];

        $this->validate($rules);

        $service->convertToOrder($this->quotationId, [
            'order_number' => $this->order_number,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date ?: null,
            'notes' => $this->conversion_notes,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => __('sales::sales.convert_to_order') . ' ' . __('sales::sales.success_created')]);
        $this->showConvertModal = false;
    }

    public function render(QuotationService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'customer_id' => $this->customerFilter,
        ];

        $quotations = $service->listQuotations($filters, 10);
        $customers = Customer::all();
        $products = Product::active()->get();
        $branches = Branch::all();

        return view('sales::livewire.admin.quotations.index', [
            'quotations' => $quotations,
            'customers' => $customers,
            'products' => $products,
            'branches' => $branches,
        ])->title(__('sales::sales.quotations'));
    }
}
