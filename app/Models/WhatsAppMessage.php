<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'tenant_id', 'bill_id', 'phone', 'template', 'locale',
        'message', 'variables', 'status', 'sent_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'sent_at' => 'datetime',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bill(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public static function deepLink(string $phone): string
    {
        return 'https://wa.me/'.preg_replace('/[^0-9]/', '', $phone);
    }
}
