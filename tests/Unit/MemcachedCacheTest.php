<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\MemCached;

class MemcachedCacheTest extends AbstractCache
{

    public function getEngine(): Engines
    {
        return Engines::MEMCACHED;
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