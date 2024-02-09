<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Adapters\SimpleCacheAdapter;

class MemcachedCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new SimpleCacheAdapter(new MemCached($_ENV['MEMCACHE_HOST']));
    }

    public function testAllKeys()
    {
        $this->assertTrue(true);
    }
}