<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenancy extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'move_in_date', 'move_out_date',
        'monthly_rent', 'deposit', 'status', 'notes', 'is_deleted',
    ];

    protected $casts = [
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'deposit' => 'decimal:2',
        'is_deleted' => 'boolean',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function leases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function rentChanges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RentChange::class);
    }

    public function moveOut(?string $notes = null): void
    {
        $this->update([
            'move_out_date' => now()->toDateString(),
            'status' => 'ended',
            'notes' => $notes ?? $this->notes,
        ]);

        if ($this->unit) {
            $this->unit->markVacant("Tenancy ended for {$this->tenant?->full_name}");
        }
    }
}
