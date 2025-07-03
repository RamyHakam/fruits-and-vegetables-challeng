<?php

namespace App\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\Quantity;
use App\Domain\Enum\ProduceUnitType;

class QuantityTest extends TestCase
{
    public function testConstructorRejectsNegativeGrams(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Quantity(-1.0);
    }

    public function testFromGrams(): void
    {
        $quantity = Quantity::from(500.0, ProduceUnitType::GRAM);

        $this->assertInstanceOf(Quantity::class, $quantity);
        // Internally stored in grams
        $this->assertEquals(500.0, $quantity->toGrams());
        $this->assertEquals(0.5,   $quantity->toKilograms());
    }

    public function testFromKilograms(): void
    {
        $quantity = Quantity::from(1.75, ProduceUnitType::KILOGRAM);

        // 1.75 kg -> 1750 g
        $this->assertEquals(1750.0, $quantity->toGrams());
        $this->assertEquals(1.75,   $quantity->toKilograms());
    }

    public function testZeroQuantity(): void
    {
        $zeroGram = Quantity::from(0.0, ProduceUnitType::GRAM);
        $this->assertEquals(0.0, $zeroGram->toGrams());

        $zeroKg = Quantity::from(0.0, ProduceUnitType::KILOGRAM);
        $this->assertEquals(0.0, $zeroKg->toGrams());
        $this->assertEquals(0.0, $zeroKg->toKilograms());
    }
}
