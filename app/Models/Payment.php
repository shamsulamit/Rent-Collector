<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'tenant_id', 'tenancy_id', 'property_id', 'unit_id', 'amount',
        'payment_date', 'method', 'reference', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenancy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function property(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function allocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function recorder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function unallocated(): float
    {
        $allocated = array_key_exists('allocated_sum', $this->attributes)
            ? (float) ($this->attributes['allocated_sum'] ?? 0)
            : ($this->relationLoaded('allocations')
                ? (float) $this->allocations->sum('amount')
                : (float) $this->allocations()->sum('amount'));

        return round((float) $this->amount - $allocated, 2);
    }
}
