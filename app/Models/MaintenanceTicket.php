<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'ticket_no', 'property_id', 'unit_id', 'tenant_id', 'vendor_id',
        'priority', 'title', 'description', 'estimated_cost', 'actual_cost',
        'status', 'opened_at', 'completed_at', 'cancelled_at', 'notes',
        'assigned_to',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'opened_at' => 'date',
        'completed_at' => 'date',
        'cancelled_at' => 'date',
    ];

    public function property(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public static function nextTicketNumber(): string
    {
        $last = self::query()->latest('id')->value('ticket_no');

        return 'MT-'.str_pad(((int) str_replace('MT-', '', $last ?? 'MT-0')) + 1, 5, '0', STR_PAD_LEFT);
    }
}
