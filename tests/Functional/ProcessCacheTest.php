<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;

class ProcessCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new PsrSimpleCacheAdapter(new ProcessCache('test_cache'));
    }
}