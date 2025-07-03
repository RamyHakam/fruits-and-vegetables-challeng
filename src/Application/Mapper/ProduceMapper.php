<?php

namespace App\Application\Mapper;

use App\Application\DTO\ProduceDTO;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Model\Produce;

final class ProduceMapper
{
    /**
     * @return ProduceDTO[]
     */
    public function toDtoList(array $produces, ProduceUnitType $outUnit): array
    {
        return array_map(
            fn(Produce $p) => $this->toDto($p, $outUnit),
            $produces
        );
    }

    public function toDto(Produce $produce, ProduceUnitType $outUnit): ProduceDTO
    {
        $qty =  ProduceUnitType::GRAM === $outUnit
            ? $produce->getQuantity()->toGrams()
            : $produce->getQuantity()->toKilograms();

        return new ProduceDTO(
            $produce->getId(),
            $produce->getName(),
            $produce->getType(),
            $qty,
            $outUnit,
        );
    }
}