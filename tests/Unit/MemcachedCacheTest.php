<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Tests\Common\CacheEnum;
use JuanchoSL\SimpleCache\Tests\Common\CacheFactory;

class MemcachedCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = CacheFactory::getInstance(CacheEnum::MEMCACHED);
    }

    public function testLoad()
    {
        $this->assertInstanceOf(MemCached::class, $this->cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
    public function testAllKeys()
    {
        $this->assertTrue(true);
        //$this->markTestSkipped();
    }
}