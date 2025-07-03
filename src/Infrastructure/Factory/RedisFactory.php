<?php

namespace App\Infrastructure\Factory;

use Redis;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class RedisFactory
{
    public function __construct(
        #[Autowire('%env(REDIS_HOST)%')]
        private string $host = '127.0.0.1',

        #[Autowire('%env(int:REDIS_PORT)%')]
        private int    $port = 6379,
    ) {}

    public  function __invoke(): Redis
    {
        $redis = new Redis();
        $redis->connect($this->host, $this->port);
        return $redis;
    }

}