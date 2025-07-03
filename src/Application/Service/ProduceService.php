<?php

namespace App\Application\Service;

use App\Application\DTO\ProduceDTO;
use App\Application\DTO\ProduceListFiltersDTO;
use App\Application\Port\ProduceStorageInterface;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Model\Produce;
use App\Domain\Model\ProduceCollection;


final  readonly  class ProduceService
{
    public function __construct(
        private ProduceStorageInterface $storage,
    ) {}

    public function add(ProduceDTO $produceDTO): void
    {
        $collection = $this->storage->load($produceDTO->type) ?? new ProduceCollection($produceDTO->type);

        $collection->add(...$produceDTO->toArray());

        $this->storage->save($collection);
    }

    public function remove(ProduceType $type, int $id): bool
    {
        $collection = $this->storage->load($type) ;
        $result = false;
        if($collection)
        {
            $result = $collection->remove($id);
            $this->storage->save($collection);
        }
        return $result;
    }

    public function list(ProduceType $type, ProduceListFiltersDTO $filtersDTO , ProduceUnitType $returnUnit = ProduceUnitType::GRAM   ): array
    {
        $collection = $this->storage->load($type) ?? new ProduceCollection($type);
        $predicate = $this->buildFilterPredicate($filtersDTO, $returnUnit);
        return $collection->list($predicate);
    }

    public function getOne(ProduceType $type, int $id): ?Produce
    {
        $collection = $this->storage->load($type);
        return $collection?->findById($id);
    }

    private function buildFilterPredicate(
        ProduceListFiltersDTO $filters,
        ProduceUnitType       $returnUnit
    ): callable {
        return function (Produce $produce) use ($filters, $returnUnit): bool {
            $produceQuantity = $returnUnit === ProduceUnitType::GRAM
                ? $produce->getQuantity()->toGrams()
                : $produce->getQuantity()->toKilograms();

            if ($filters->minQuantity !== null && $produceQuantity < $filters->minQuantity) {
                return false;
            }
            if ($filters->maxQuantity !== null && $produceQuantity > $filters->maxQuantity) {
                return false;
            }
            if ($filters->name !== null
                && strcasecmp($produce->getName(), $filters->name) !== 0
            ) {
                return false;
            }
            return true;
        };
    }
}
