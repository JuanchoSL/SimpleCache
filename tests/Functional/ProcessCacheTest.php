<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Adapters\SimpleCacheAdapter;

class ProcessCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new SimpleCacheAdapter(new ProcessCache('test_cache'));
    }
}