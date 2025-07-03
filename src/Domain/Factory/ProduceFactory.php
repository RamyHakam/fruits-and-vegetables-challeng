<?php

namespace App\Domain\Factory;

use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Model\Produce;
use App\Domain\ValueObject\Quantity;

class ProduceFactory
{
    public static function create(
        int $id,
        string $name,
        ProduceType $type,
        float $quantity,
        ProduceUnitType $unit
    ): Produce
    {
        return new Produce(
            id: $id,
            name: $name,
            type: $type,
            quantity: Quantity::from($quantity, $unit),
        );
    }
}
