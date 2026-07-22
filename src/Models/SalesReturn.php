<?php

namespace Dev3bdulrahman\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Dev3bdulrahman\Crm\Models\Customer;
use App\Models\User;

class SalesReturn extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'sales_returns';

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'invoice_id',
        'return_number',
        'return_date',
        'status',
        'subtotal',
        'tax_total',
        'discount_total',
        'grand_total',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'subtotal' => 'decimal:4',
        'tax_total' => 'decimal:4',
        'discount_total' => 'decimal:4',
        'grand_total' => 'decimal:4',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
