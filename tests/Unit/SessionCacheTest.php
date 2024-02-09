<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\SessionCache;

class SessionCacheTest extends AbstractCache
{
    
    public function setUp(): void
    {
        $this->cache = new SessionCache('test_cache');
    }
    public function testLoad()
    {
        $this->assertInstanceOf(SessionCache::class, $this->cache);
        //$this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}