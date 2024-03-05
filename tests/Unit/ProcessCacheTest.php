<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Tests\Common\CacheEnum;
use JuanchoSL\SimpleCache\Tests\Common\CacheFactory;

class ProcessCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = CacheFactory::getInstance(CacheEnum::PROCESS);
    }

    public function testLoad()
    {
        $this->assertInstanceOf(ProcessCache::class, $this->cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }

}