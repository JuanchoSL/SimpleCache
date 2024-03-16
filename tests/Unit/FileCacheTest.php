<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\FileCache;

class FileCacheTest extends AbstractCache
{

    public function getEngine(): Engines
    {
        return Engines::FILE;
    }

    public function testLoad()
    {
        $this->assertInstanceOf(FileCache::class, $this->cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}