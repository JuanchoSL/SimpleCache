<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\RedisCache;

class RedisCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = new RedisCache($_ENV['REDIS_HOST']);
    }

    public function testLoad()
    {
        $this->assertInstanceOf(RedisCache::class, $this->cache);
        //$this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}