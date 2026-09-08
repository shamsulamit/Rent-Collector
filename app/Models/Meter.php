<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meter extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'property_id', 'floor_id', 'unit_id', 'utility_type_id', 'meter_number',
        'provider', 'meter_type', 'utility', 'measurement_unit', 'installation_date',
        'starting_reading', 'unit_price', 'status', 'notes', 'is_deleted',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'starting_reading' => 'decimal:2',
        'unit_price' => 'decimal:4',
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

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function utilityType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function readings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function electricityBills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ElectricityBill::class);
    }

    public function gasBills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GasBill::class);
    }

    public function waterBills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WaterBill::class);
    }

    public function prepaidRecharges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PrepaidRecharge::class);
    }

    public function label(): string
    {
        return collect([
            $this->meter_number,
            $this->unit?->name,
            $this->property?->name,
        ])->filter()->implode(' · ');
    }

    public function lastReading(?string $beforeMonth = null): ?MeterReading
    {
        $query = $this->readings()->where('status', '!=', 'draft');
        if ($beforeMonth) {
            $query->where('billing_month', '<', $beforeMonth);
        }

        return $query->orderByDesc('billing_month')->first();
    }

    public function latestReading(): ?MeterReading
    {
        return $this->readings()->orderByDesc('billing_month')->first();
    }
}
