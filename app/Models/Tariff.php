<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name', 'provider', 'utility', 'meter_type', 'effective_date',
        'expiry_date', 'slabs', 'fixed_charge', 'service_charge', 'demand_charge',
        'vat_rate', 'other_charge', 'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'slabs' => 'array',
        'fixed_charge' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'demand_charge' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'other_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ElectricityBill::class);
    }

    public function scopeForDate($query, $date, string $utility = 'electricity', string $meterType = 'postpaid')
    {
        $date = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date;

        return $query->where('utility', $utility)
            ->where('meter_type', $meterType)
            ->where(fn ($q) => $q->whereNull('effective_date')->orWhere('effective_date', '<=', $date))
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $date))
            ->orderByDesc('effective_date');
    }

    /**
     * Calculate the energy charge for a given usage using inclusive consumption slabs.
     * Example: 160 units across 0–75 then 76–200 bills 75 + 85 units.
     */
    public function calculateEnergy(float $usage): float
    {
        if ($usage <= 0 || empty($this->slabs)) {
            return 0.0;
        }

        $total = 0.0;
        $billedThrough = 0.0;
        $slabs = collect($this->slabs)->sortBy('min')->values();

        foreach ($slabs as $slab) {
            if ($billedThrough >= $usage) {
                break;
            }

            $max = ! isset($slab['max']) || $slab['max'] === '' || $slab['max'] === null
                ? $usage
                : (float) $slab['max'];
            $rate = (float) ($slab['rate'] ?? 0);
            $slice = max(0, min($usage, $max) - $billedThrough);
            $total += $slice * $rate;
            $billedThrough += $slice;
        }

        return round($total, 2);
    }
}
