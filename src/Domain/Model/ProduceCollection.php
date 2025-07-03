<?php

namespace App\Domain\Model;

use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Exception\ProduceTypeMismatchException;
use App\Domain\Factory\ProduceFactory;

final   class ProduceCollection implements ProduceCollectionInterface
{
    public function __construct(
        private readonly ProduceType $produceType,
        private array $produces = [],
    ) {}

    public function getProduceType(): ProduceType
    {
        return $this->produceType;
    }

    public function add(
        string $name,
        ProduceType $type,
        float $quantity,
        ProduceUnitType $unit,
        ?int $id = null,
    ): Produce {

        if ($type !== $this->produceType) {
            throw new ProduceTypeMismatchException($this->produceType, $type);
        }

        // ToDo - add  to the existing produce if the id is already set

        $id = $id??  $this->nextId();

        $produce = ProduceFactory::create(
            id: $id,
            name: $name,
            type: $type,
            quantity: $quantity,
            unit: $unit,
        );

        $this->produces[$id] = $produce;

        return $produce;
    }

    public function list(?callable $predicate = null ): array
    {
        if ($predicate === null) {
            return $this->produces;
        }
        return array_values(array_filter($this->produces, $predicate));
    }

    public function remove(int $id): bool
    {
        if (isset($this->produces[$id])) {
            unset($this->produces[$id]);
            return true;
        }
        return false;
    }

    public function nextId(): int
    {
        if (empty($this->produces)) {
            return 1;
        }
        return max(array_keys($this->produces)) + 1;
    }

    public function findById(int $id): ?Produce
    {
        return $this->produces[$id] ?? null;
    }

    /**
     * @return Produce[]
     */
    public function search(callable $predicate): array
    {
        return array_values(array_filter($this->produces, $predicate));
    }
}