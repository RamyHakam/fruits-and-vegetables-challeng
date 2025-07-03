<?php

namespace App\Application\Service;

use App\Application\DTO\ProduceDTO;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Model\Produce;

final class ProducePresenter
{
    public static function toDto(Produce $p, ProduceUnitType $returnUnit): ProduceDTO
    {
        $quantity = $returnUnit === ProduceUnitType::GRAM
            ? $p->getQuantity()->toGrams()
            : $p->getQuantity()->toKilograms();

        return new ProduceDTO(
            id:       $p->getId(),
            name:     $p->getName(),
            type:     $p->getType(),
            quantity: $quantity,
            unit:     $returnUnit,
        );
    }
}