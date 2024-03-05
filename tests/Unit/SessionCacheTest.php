<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\SessionCache;
use JuanchoSL\SimpleCache\Tests\Common\CacheEnum;
use JuanchoSL\SimpleCache\Tests\Common\CacheFactory;

class SessionCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        //$this->cache = new SessionCache('test_cache');
        $this->cache = CacheFactory::getInstance(CacheEnum::SESSION);
    }
    public function testLoad()
    {
        $this->assertInstanceOf(SessionCache::class, $this->cache);
        //$this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}