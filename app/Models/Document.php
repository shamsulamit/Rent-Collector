<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'documentable_type', 'documentable_id', 'type', 'title', 'file_path',
        'mime_type', 'size', 'notes', 'uploaded_by',
    ];

    public function documentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk('local')->url($this->file_path);
    }

    public static function store(string $entityType, string $entityId, $file, string $type, ?string $title = null, ?string $notes = null): self
    {
        $path = $file->store('documents/'.$entityType, 'local');

        return self::create([
            'documentable_type' => $entityType,
            'documentable_id' => $entityId,
            'type' => $type,
            'title' => $title,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'notes' => $notes,
            'uploaded_by' => Auth::id(),
        ]);
    }
}
