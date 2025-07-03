<?php

namespace App\Domain\Model;

use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;

interface ProduceCollectionInterface
{
    public function add(
        string          $name,
        ProduceType     $type,
        float           $quantity,
        ProduceUnitType $unit,
        ?int            $id = null,
    ): Produce;

    public function remove(int $id): bool;

    /** @return Produce[] */
    public function list(?callable $predicate = null ): array;

    public function findById(int $id): ?Produce;

    public function search(callable $predicate): array;
}
