<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'property_id', 'floor_id', 'name', 'unit_type', 'size', 'bedrooms',
        'bathrooms', 'monthly_rent', 'status', 'notes', 'is_deleted',
    ];

    protected $casts = [
        'size' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
        'is_deleted' => 'boolean',
    ];

    public function property(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function floor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function tenancies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    public function activeTenancy(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Tenancy::class)->where('status', 'active');
    }

    public function currentTenant()
    {
        return $this->activeTenancy?->tenant;
    }

    public function meters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Meter::class);
    }

    public function bills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function vacancyHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnitVacancyHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function markOccupied(?string $tenantName = null): void
    {
        $this->update(['status' => 'occupied']);
        if ($tenantName) {
            $this->vacancyHistory()->create([
                'occupied_on' => now()->toDateString(),
                'tenant_name' => $tenantName,
            ]);
        }
    }

    public function markVacant(?string $notes = null): void
    {
        $this->update(['status' => 'vacant']);
        $this->vacancyHistory()->create([
            'vacated_on' => now()->toDateString(),
            'notes' => $notes,
        ]);
    }
}
