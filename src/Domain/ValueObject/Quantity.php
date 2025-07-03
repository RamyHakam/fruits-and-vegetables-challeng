<?php

namespace App\Domain\ValueObject;

use App\Domain\Enum\ProduceUnitType;

final  class Quantity
{
    public function __construct(
        public float $grams
    ) {
        if ($grams < 0) {
            throw new \InvalidArgumentException('Quantity must be ≥ 0');
        }
    }

    public static function from(float $amount, ProduceUnitType $unit): self
    {
        $grams = match ($unit) {
            ProduceUnitType::GRAM     => $amount,
            ProduceUnitType::KILOGRAM => $amount * 1000,
        };

        return new self($grams);
    }

    public function toGrams(): float
    {
        return $this->grams;
    }

    public function toKilograms(): float
    {
        return $this->grams / 1000;
    }
}