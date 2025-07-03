<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Model\ProduceCollectionInterface;
use Symfony\Contracts\Cache\CacheInterface;

final  readonly class FileSystemProduceStorage
{
    public function __construct(
        private CacheInterface $produceCache,
    ) {}

    public function save(ProduceCollectionInterface $collection): void
    {
        $key = $this->key($collection->getProduceType());

        $this->produceCache->delete($key);

        $this->produceCache->set($key, fn () => $collection);
    }

    public function load(ProduceType $type): ?ProduceCollectionInterface
    {
        $key = $this->key($type);

        return $this->produceCache->get($key, fn () => null);
    }

    private function key(ProduceType $type): string
    {
        return 'produce_collection:' . strtolower($type->value);
    }

}