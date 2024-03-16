<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\ProcessCache;

class ProcessCacheTest extends AbstractCache
{

    public function getEngine(): Engines
    {
        return Engines::PROCESS;
    }
    public function testLoad()
    {
        $this->assertInstanceOf(ProcessCache::class, $this->cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }

}