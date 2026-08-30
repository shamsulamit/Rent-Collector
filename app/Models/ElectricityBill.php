<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricityBill extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'property_id', 'unit_id', 'tenant_id', 'tenancy_id', 'meter_id', 'tariff_id',
        'billing_month', 'previous_reading', 'current_reading', 'usage', 'energy_charge',
        'fixed_charge', 'service_charge', 'demand_charge', 'vat', 'other_charge',
        'discount', 'adjustment', 'total', 'status', 'finalized_at', 'finalized_by',
    ];

    protected $casts = [
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'usage' => 'decimal:2',
        'energy_charge' => 'decimal:2',
        'fixed_charge' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'demand_charge' => 'decimal:2',
        'vat' => 'decimal:2',
        'other_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'total' => 'decimal:2',
        'finalized_at' => 'date',
    ];

    public function property(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenancy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function meter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function tariff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function isImmutable(): bool
    {
        return in_array($this->status, ['finalized', 'paid', 'partial', 'due', 'overpaid']);
    }
}
