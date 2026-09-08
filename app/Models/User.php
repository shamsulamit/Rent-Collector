<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'theme',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public const ROLES = [
        'owner' => 'Owner',
        'manager' => 'Manager',
        'accountant' => 'Accountant',
        'staff' => 'Staff',
    ];

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Tenant::class, 'email', 'email');
    }

    public function maintenanceTickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MaintenanceTicket::class, 'assigned_to');
    }
}
