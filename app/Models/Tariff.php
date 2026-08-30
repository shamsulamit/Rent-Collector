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

    public function scopeForDate($query, string $date, string $utility = 'electricity', string $meterType = 'postpaid')
    {
        return $query->where('utility', $utility)
            ->where('meter_type', $meterType)
            ->where(fn ($q) => $q->whereNull('effective_date')->orWhere('effective_date', '<=', $date))
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $date))
            ->orderByDesc('effective_date');
    }

    /**
     * Calculate the energy charge for a given usage using slab rates.
     */
    public function calculateEnergy(float $usage): float
    {
        $total = 0.0;
        $remaining = $usage;

        if (empty($this->slabs)) {
            return 0.0;
        }

        $slabs = collect($this->slabs)->sortBy('min')->values();

        foreach ($slabs as $slab) {
            if ($remaining <= 0) {
                break;
            }
            $min = (float) ($slab['min'] ?? 0);
            $max = isset($slab['max']) ? (float) $slab['max'] : PHP_FLOAT_MAX;
            $rate = (float) ($slab['rate'] ?? 0);
            $band = min($remaining, $max - $min);
            $total += $band * $rate;
            $remaining -= $band;
        }

        return round($total, 2);
    }
}
