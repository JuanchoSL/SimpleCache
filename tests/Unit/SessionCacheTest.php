<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\SessionCache;

class SessionCacheTest extends AbstractCache
{

    public function getEngine(): Engines
    {
        return Engines::SESSION;
    }

    public function testLoad()
    {
        $this->assertInstanceOf(SessionCache::class, $this->cache);
        //$this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}