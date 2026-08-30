<?php

namespace App\Enums;

enum UnitStatus: string
{
    case Vacant = 'vacant';
    case Occupied = 'occupied';
    case Reserved = 'reserved';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Vacant => 'Vacant',
            self::Occupied => 'Occupied',
            self::Reserved => 'Reserved',
            self::Maintenance => 'Maintenance',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Vacant => 'gray',
            self::Occupied => 'emerald',
            self::Reserved => 'amber',
            self::Maintenance => 'orange',
            self::Inactive => 'slate',
        };
    }
}
