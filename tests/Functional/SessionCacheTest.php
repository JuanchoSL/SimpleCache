<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;
use JuanchoSL\SimpleCache\Repositories\SessionCache;

class SessionCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new PsrSimpleCacheAdapter(new SessionCache('test_cache'));
    }
}