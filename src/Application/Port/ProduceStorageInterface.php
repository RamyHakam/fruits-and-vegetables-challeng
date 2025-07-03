<?php

namespace App\Application\Port;

use App\Domain\Enum\ProduceType;
use App\Domain\Model\ProduceCollectionInterface;

interface ProduceStorageInterface
{
    public function save(ProduceCollectionInterface $collection): void;
    public function load(ProduceType $produceType): ?ProduceCollectionInterface;

}