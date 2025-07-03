<?php

namespace App\Domain\Model;

use App\Domain\Enum\ProduceType;
use App\Domain\ValueObject\Quantity;

final  readonly  class Produce
{
    public function __construct(
        private int $id,
        private string    $name,
        private ProduceType  $type,
        private Quantity      $quantity,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ProduceType
    {
        return $this->type;
    }
    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }
}