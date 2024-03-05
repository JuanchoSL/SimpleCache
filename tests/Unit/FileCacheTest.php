<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\FileCache;
use JuanchoSL\SimpleCache\Tests\Common\CacheEnum;
use JuanchoSL\SimpleCache\Tests\Common\CacheFactory;

class FileCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = CacheFactory::getInstance(CacheEnum::FILE);
    }

    public function testLoad()
    {
        $this->assertInstanceOf(FileCache::class, $this->cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}