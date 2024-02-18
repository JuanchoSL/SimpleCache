<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;

class RedisCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new PsrSimpleCacheAdapter(new RedisCache($_ENV['REDIS_HOST']));
    }
}