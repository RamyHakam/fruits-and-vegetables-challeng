<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Enum\ProduceType;
use App\Domain\Model\ProduceCollectionInterface;
use Redis;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final  readonly class RedisProduceStorage
{
    public function __construct(
        #[Autowire(service: 'RedisStorageService')]
        private Redis $redis,
    ) {}

    public function save(ProduceCollectionInterface $collection): void
    {
        $key = $this->key($collection->getProduceType());
        $this->redis->set($key, serialize($collection));
    }

    public function load(ProduceType $type): ?ProduceCollectionInterface
    {
        $data = $this->redis->get($this->key($type));
        return $data ? unserialize($data) : null;
    }

    private function key(ProduceType $type): string
    {
        return 'produce_collection:' . strtolower($type->value);
    }
}
