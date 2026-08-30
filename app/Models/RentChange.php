<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentChange extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'tenancy_id', 'unit_id', 'tenant_id', 'user_id', 'old_rent', 'new_rent',
        'effective_date', 'proration_method', 'reason',
    ];

    protected $casts = [
        'old_rent' => 'decimal:2',
        'new_rent' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function tenancy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
