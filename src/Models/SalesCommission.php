<?php

namespace Dev3bdulrahman\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SalesCommission extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'sales_commissions';

    protected $fillable = [
        'company_id',
        'user_id',
        'invoice_id',
        'sales_order_id',
        'amount',
        'rate',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
}
