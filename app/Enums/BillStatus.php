<?php

namespace App\Enums;

enum BillStatus: string
{
    case Draft = 'draft';
    case Calculated = 'calculated';
    case Finalized = 'finalized';
    case Paid = 'paid';
    case Partial = 'partial';
    case Due = 'due';
    case Overpaid = 'overpaid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Calculated => 'Calculated',
            self::Finalized => 'Finalized',
            self::Paid => 'Paid',
            self::Partial => 'Partial',
            self::Due => 'Due',
            self::Overpaid => 'Overpaid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Calculated => 'sky',
            self::Finalized => 'indigo',
            self::Paid => 'emerald',
            self::Partial => 'amber',
            self::Due => 'orange',
            self::Overpaid => 'teal',
        };
    }
}
