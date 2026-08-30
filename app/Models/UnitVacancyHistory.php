<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitVacancyHistory extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'unit_vacancy_history';

    protected $fillable = ['unit_id', 'vacated_on', 'occupied_on', 'tenant_name', 'notes'];

    protected $casts = [
        'vacated_on' => 'date',
        'occupied_on' => 'date',
    ];

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
