<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum ProduceType: string
{
    case FRUIT = 'fruit';
    case VEGETABLE = 'vegetable';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
