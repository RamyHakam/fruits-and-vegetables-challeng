<?php

namespace App\Tests\Unit\Domain\Model;

use App\Domain\Exception\ProduceTypeMismatchException;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Model\Produce;
use App\Domain\Model\ProduceCollection;
use PHPUnit\Framework\TestCase;
class ProduceCollectionTest extends TestCase
{
    public function testGetProduceType(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $this->assertSame(ProduceType::FRUIT, $collection->getProduceType());
    }

    public function testAddGeneratesIdWhenNotProvided(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);

        $produceA = $collection->add(
            name: 'Apple',
            type: ProduceType::FRUIT,
            quantity: 100,
            unit: ProduceUnitType::GRAM
        );
        $this->assertSame(1, $produceA->getId());

        $produceB = $collection->add(
            name: 'Banana',
            type: ProduceType::FRUIT,
            quantity: 1,
            unit: ProduceUnitType::KILOGRAM
        );
        // Next id should increment
        $this->assertSame(2, $produceB->getId());
    }

    public function testAddUsesProvidedId(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $produce = $collection->add(
            name: 'Cherry',
            type: ProduceType::FRUIT,
            quantity: 50,
            unit: ProduceUnitType::GRAM,
            id: 42
        );
        $this->assertSame(42, $produce->getId());
    }

    public function testQuantitySavedInGrams(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $produce = $collection->add(
            name: 'Cherry',
            type: ProduceType::FRUIT,
            quantity: 50,
            unit: ProduceUnitType::GRAM,
            id: 42
        );
        $this->assertSame(50.0, $produce->getQuantity()->toGrams());
    }

    public function testQuantitySavedInGramsWithKGUnit(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $produce = $collection->add(
            name: 'Cherry',
            type: ProduceType::FRUIT,
            quantity: 50,
            unit: ProduceUnitType::KILOGRAM,
            id: 42
        );
        $this->assertSame(50000.0, $produce->getQuantity()->toGrams());
    }

    public function testListContainsAllAddedProduces(): void
    {
        $collection = new ProduceCollection(ProduceType::VEGETABLE);
        $collection->add('Carrot', ProduceType::VEGETABLE, 200, ProduceUnitType::GRAM);
        $collection->add('Cabbage', ProduceType::VEGETABLE, 1, ProduceUnitType::KILOGRAM);

        $list = $collection->list();
        $this->assertCount(2, $list);
    }

    public function testRemoveReturnsTrueAndRemoves(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $p = $collection->add('Apple', ProduceType::FRUIT, 100, ProduceUnitType::GRAM);

        $this->assertTrue($collection->remove($p->getId()));
        $this->assertSame([], $collection->list());
    }

    public function testRemoveReturnsFalseForUnknownId(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $this->assertFalse($collection->remove(999));
    }

    public function testFindByIdReturnsCorrectProduceOrNull(): void
    {
        $collection = new ProduceCollection(ProduceType::VEGETABLE);
        $p = $collection->add('Pepper', ProduceType::VEGETABLE, 500, ProduceUnitType::GRAM);

        $found = $collection->findById($p->getId());
        $this->assertSame($p, $found);

        $this->assertNull($collection->findById(1234));
    }

    public function testNextIdOnEmptyCollection(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        // Should be 1 if empty
        $this->assertSame(1, $collection->nextId());
    }

    public function testSearchFiltersCorrectly(): void
    {
        $collection = new ProduceCollection(ProduceType::VEGETABLE);
        $a = $collection->add('A', ProduceType::VEGETABLE, 100, ProduceUnitType::GRAM);
        $b = $collection->add('B', ProduceType::VEGETABLE, 2, ProduceUnitType::KILOGRAM);
        $c = $collection->add('C', ProduceType::VEGETABLE, 50, ProduceUnitType::GRAM);

        // search for >1000 grams (should return only B)
        /* @ var Produce[] $results */
        $results = $collection->search(fn($p) => $p->getQuantity()->toGrams() > 1000);
        $this->assertCount(1, $results);
        $this->assertEquals($b->getName(), $results[0]->getName());
        $this->assertEquals($b->getType(), $results[0]->getType());
    }

    public function testAddThrowsOnTypeMismatch(): void
    {
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $this->expectException(ProduceTypeMismatchException::class);
        $collection->add('Broccoli', ProduceType::VEGETABLE, 100, ProduceUnitType::GRAM);
    }

    /**
     * @dataProvider provideFilters
     */
    public function testListWithVariousFilters(
        callable $predicate,
        array    $expectedNames
    ): void {
        // Create a fresh collection for each case
        $collection = new ProduceCollection(ProduceType::FRUIT);
        $collection->add('Apple',  ProduceType::FRUIT, 5,  ProduceUnitType::KILOGRAM);
        $collection->add('Banana', ProduceType::FRUIT, 100, ProduceUnitType::GRAM);
        $collection->add('Cherry', ProduceType::FRUIT, 2,  ProduceUnitType::KILOGRAM);

        $result = $collection->list($predicate);
        $names  = array_map(fn(Produce $p) => $p->getName(), $result);

        $this->assertSame(
            $expectedNames,
            $names
        );
    }

    public static function provideFilters(): array
    {
        return [
            'minQuantity>=3kg' => [
                fn(Produce $p): bool =>
                    $p->getQuantity()->toKilograms() >= 3.0,
                ['Apple'],
            ],
            'maxQuantity<=2kg' => [
                fn(Produce $p): bool =>
                    $p->getQuantity()->toKilograms() <= 2.0,
                ['Banana', 'Cherry'],
            ],
            'nameExactBanana'  => [
                fn(Produce $p): bool =>
                    strcasecmp($p->getName(), 'banana') === 0,
                ['Banana'],
            ],
        ];
    }
}