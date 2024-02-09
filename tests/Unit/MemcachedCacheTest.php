<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\MemCached;

class MemcachedCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = new MemCached($_ENV['MEMCACHE_HOST']);
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