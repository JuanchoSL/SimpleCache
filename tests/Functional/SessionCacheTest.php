<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Adapters\SimpleCacheAdapter;
use JuanchoSL\SimpleCache\Repositories\SessionCache;

class SessionCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new SimpleCacheAdapter(new SessionCache('test_cache'));
    }
}