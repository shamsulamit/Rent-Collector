<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name', 'address', 'city', 'area', 'postal_code', 'contact_phone',
        'contact_email', 'description', 'status', 'notes', 'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function floors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Floor::class);
    }

    public function units(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function tenancies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    public function bills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function meters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Meter::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_deleted', false);
    }

    public function occupiedUnits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->units()->where('status', 'occupied');
    }

    public function vacancyRate(): float
    {
        $total = $this->units()->count();
        if ($total === 0) {
            return 0;
        }
        $occupied = $this->units()->where('status', 'occupied')->count();

        return round(($occupied / $total) * 100, 1);
    }
}
