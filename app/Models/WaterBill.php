<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterBill extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'meter_id', 'property_id', 'unit_id', 'tenant_id', 'tenancy_id',
        'billing_month', 'previous_reading', 'current_reading', 'usage',
        'rate', 'charge', 'photo_path', 'status',
    ];

    protected $casts = [
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'usage' => 'decimal:2',
        'rate' => 'decimal:2',
        'charge' => 'decimal:2',
    ];

    public function meter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
