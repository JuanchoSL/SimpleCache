<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\ProcessCache;

class ProcessCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = new ProcessCache('test_cache');
    }

    public function testLoad()
    {
        $this->assertInstanceOf(ProcessCache::class, $this->cache);
       // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }

}