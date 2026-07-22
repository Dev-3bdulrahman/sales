<?php

namespace Dev3bdulrahman\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Discount extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'sales_discounts';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'type',
        'value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
