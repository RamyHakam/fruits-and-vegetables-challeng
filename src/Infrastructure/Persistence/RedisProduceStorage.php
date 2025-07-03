<?php

namespace App\Infrastructure\Persistence;

use App\Application\Port\ProduceStorageInterface;
use App\Domain\Enum\ProduceType;
use App\Domain\Model\ProduceCollectionInterface;
use Redis;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(ProduceStorageInterface::class)]
final  readonly class RedisProduceStorage  implements ProduceStorageInterface
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

    public function load(ProduceType $produceType): ?ProduceCollectionInterface
    {
        $data = $this->redis->get($this->key($produceType));
        return $data ? unserialize($data) : null;
    }

    private function key(ProduceType $type): string
    {
        return 'produce_collection:' . strtolower($type->value);
    }
}
