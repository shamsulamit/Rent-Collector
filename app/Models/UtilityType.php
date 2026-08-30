<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityType extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['name', 'type', 'is_metered', 'is_active'];

    protected $casts = [
        'is_metered' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function meters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Meter::class);
    }
}
