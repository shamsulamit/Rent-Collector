<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['bill_id', 'type', 'label', 'amount', 'sort'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function bill(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
