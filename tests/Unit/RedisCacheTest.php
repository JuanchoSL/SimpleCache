<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Tests\Common\CacheEnum;
use JuanchoSL\SimpleCache\Tests\Common\CacheFactory;

class RedisCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = CacheFactory::getInstance(CacheEnum::REDIS);
    }

    public function testLoad()
    {
        $this->assertInstanceOf(RedisCache::class, $this->cache);
        //$this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}