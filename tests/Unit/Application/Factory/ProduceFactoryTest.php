<?php

namespace App\Tests\Unit\Application\Factory;

use App\Application\DTO\ProduceDTO;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Factory\ProduceFactory;
use PHPUnit\Framework\TestCase;

class ProduceFactoryTest extends TestCase
{
    private ProduceFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ProduceFactory();
    }

    public function testFromDtoWithKilograms(): void
    {
        $dto = new ProduceDTO(
            id: 42,
            name: 'TestFruit',
            type: ProduceType::FRUIT,
            quantity: 2.5,
            unit: ProduceUnitType::KILOGRAM
        );

        $produce = $this->factory->fromDto($dto);

        $this->assertEquals(42, $produce->getId());
        $this->assertSame('TestFruit', $produce->getName());
        $this->assertSame('fruit', $produce->getType()->value);

        $quantity = $produce->getQuantity();

        $this->assertSame(2500.0, $quantity->toGrams());
        $this->assertSame(2.5, $quantity->amount);
        $this->assertSame('kg', $quantity->unit->value);
    }

    public function testFromDtoWithGrams(): void
    {
        $dto = new ProduceDTO(
            id: 7,
            name: 'TestVeg',
            type: ProduceType::VEGETABLE,
            quantity: 500,
            unit: ProduceUnitType::GRAM
        );

        $produce = $this->factory->fromDto($dto);

        $this->assertEquals(7, $produce->getId());
        $this->assertSame('TestVeg', $produce->getName());
        $this->assertSame('vegetable', $produce->getType()->value);

        $quantity = $produce->getQuantity();

        $this->assertSame(500.0, $quantity->toGrams());
        $this->assertSame(500.0, $quantity->amount);
        $this->assertSame('g', $quantity->unit->value);
    }
}
