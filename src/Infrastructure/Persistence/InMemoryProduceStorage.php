<?php

namespace App\Infrastructure\Persistence;

use App\Application\Port\ProduceStorageInterface;
use App\Domain\Enum\ProduceType;
use App\Domain\Model\ProduceCollectionInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ProduceStorageInterface::class ,when: 'test')]
final  class InMemoryProduceStorage implements ProduceStorageInterface
{
    private array $store = [];

    public function save(ProduceCollectionInterface $collection): void
    {
        $this->store[$collection->getProduceType()->value] = $collection;
    }

    public function load(ProduceType $produceType): ?ProduceCollectionInterface
    {
        return $this->store[$produceType->value] ?? null;
    }
}