<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupRecord extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'filename', 'disk', 'path', 'size', 'status', 'type',
        'is_encrypted', 'started_at', 'completed_at', 'restored_at',
        'created_by', 'error',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
