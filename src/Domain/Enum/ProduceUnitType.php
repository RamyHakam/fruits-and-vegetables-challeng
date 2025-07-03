<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum ProduceUnitType: string
{
    case GRAM = 'g';
    case KILOGRAM = 'kg';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
