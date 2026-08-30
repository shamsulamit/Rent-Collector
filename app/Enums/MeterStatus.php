<?php

namespace App\Enums;

enum MeterStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Closed => 'Closed',
            self::Inactive => 'Inactive',
        };
    }
}
