<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepaidRecharge extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'meter_id', 'tenant_id', 'unit_id', 'recharge_date', 'amount',
        'units', 'unit_price', 'units_after',
        'balance_after', 'reference', 'provider', 'notes', 'entered_by',
    ];

    protected $casts = [
        'recharge_date' => 'date',
        'amount' => 'decimal:2',
        'units' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'units_after' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function meter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
