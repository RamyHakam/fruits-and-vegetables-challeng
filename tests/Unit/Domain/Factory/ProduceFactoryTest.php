<?php

namespace App\Tests\Unit\Domain\Factory;

use PHPUnit\Framework\TestCase;
use App\Domain\Factory\ProduceFactory;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Model\Produce;
use App\Domain\ValueObject\Quantity;

class ProduceFactoryTest extends TestCase
{
    public function testCreateWithGrams(): void
    {
        $produce = ProduceFactory::create(
            id: 10,
            name: 'TestVegetable',
            type: ProduceType::VEGETABLE,
            quantity: 250,
            unit: ProduceUnitType::GRAM
        );

        $this->assertInstanceOf(Produce::class, $produce);
        $this->assertSame(10, $produce->getId());
        $this->assertSame('TestVegetable', $produce->getName());
        $this->assertSame(ProduceType::VEGETABLE, $produce->getType());

        $quantity = $produce->getQuantity();
        $this->assertInstanceOf(Quantity::class, $quantity);
        // Stored as grams directly
        $this->assertEquals(250.0, $quantity->toGrams());
        $this->assertEquals(0.25, $quantity->toKilograms());
    }

    public function testCreateWithKilograms(): void
    {
        $produce = ProduceFactory::create(
            id: 20,
            name: 'TestFruit',
            type: ProduceType::FRUIT,
            quantity: 1.75,
            unit: ProduceUnitType::KILOGRAM
        );

        $this->assertInstanceOf(Produce::class, $produce);
        $this->assertSame(20, $produce->getId());
        $this->assertSame('TestFruit', $produce->getName());
        $this->assertSame(ProduceType::FRUIT, $produce->getType());

        $quantity = $produce->getQuantity();
        $this->assertInstanceOf(Quantity::class, $quantity);
        // 1.75 kg -> 1750 grams
        $this->assertEquals(1750.0, $quantity->toGrams());
        $this->assertEquals(1.75, $quantity->toKilograms());
    }

    public function testCreateWithNegativeQuantityThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ProduceFactory::create(
            id: 30,
            name: 'InvalidProduce',
            type: ProduceType::FRUIT,
            quantity: -5,
            unit: ProduceUnitType::GRAM
        );
    }
}