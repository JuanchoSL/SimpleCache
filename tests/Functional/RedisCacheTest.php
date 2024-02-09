<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Adapters\SimpleCacheAdapter;

class RedisCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new SimpleCacheAdapter(new RedisCache($_ENV['REDIS_HOST']));
    }
}