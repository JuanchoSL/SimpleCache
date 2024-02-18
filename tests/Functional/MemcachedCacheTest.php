<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;

class MemcachedCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new PsrSimpleCacheAdapter(new MemCached($_ENV['MEMCACHE_HOST']));
    }

}