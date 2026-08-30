<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'property_id', 'unit_id', 'tenancy_id', 'tenant_id', 'bill_no', 'billing_month',
        'rent', 'electricity', 'gas', 'water', 'waste', 'security', 'cleaning',
        'internet', 'parking', 'other', 'discount', 'adjustment', 'total',
        'status', 'finalized_at', 'finalized_by', 'generated_by',
    ];

    protected $casts = [
        'rent' => 'decimal:2',
        'electricity' => 'decimal:2',
        'gas' => 'decimal:2',
        'water' => 'decimal:2',
        'waste' => 'decimal:2',
        'security' => 'decimal:2',
        'cleaning' => 'decimal:2',
        'internet' => 'decimal:2',
        'parking' => 'decimal:2',
        'other' => 'decimal:2',
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

    public function tenancy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_allocations')
            ->withPivot('amount');
    }

    public function allocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function finalizer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function totalPaid(): float
    {
        return (float) $this->allocations()->sum('amount');
    }

    public function balance(): float
    {
        return round((float) $this->total - $this->totalPaid(), 2);
    }

    public function paymentStatus(): string
    {
        $balance = $this->balance();

        return match (true) {
            $balance < 0 => 'overpaid',
            $balance == 0 => 'paid',
            default => 'due',
        };
    }

    public function isImmutable(): bool
    {
        return in_array($this->status, ['finalized', 'paid', 'partial', 'due', 'overpaid']);
    }

    public function lineItems(): array
    {
        $items = [];
        $map = [
            'rent' => 'Rent',
            'electricity' => 'Electricity',
            'gas' => 'Gas',
            'water' => 'Water',
            'waste' => 'Waste',
            'security' => 'Security',
            'cleaning' => 'Cleaning',
            'internet' => 'Internet',
            'parking' => 'Parking',
            'other' => 'Other',
        ];
        foreach ($map as $key => $label) {
            if ((float) $this->{$key} > 0) {
                $items[] = ['label' => $label, 'amount' => (float) $this->{$key}];
            }
        }
        if ((float) $this->discount > 0) {
            $items[] = ['label' => 'Discount', 'amount' => -1 * (float) $this->discount];
        }
        if ((float) $this->adjustment != 0) {
            $items[] = ['label' => 'Adjustment', 'amount' => (float) $this->adjustment];
        }

        return $items;
    }
}
