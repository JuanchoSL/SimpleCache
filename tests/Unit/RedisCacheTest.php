<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\RedisCache;

class RedisCacheTest extends AbstractCache
{

    public function getEngine(): Engines
    {
        return Engines::REDIS;
    }

    public function testLoad()
    {
        $this->assertInstanceOf(RedisCache::class, $this->cache);
        //$this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}