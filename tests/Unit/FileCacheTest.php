<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Repositories\FileCache;

class FileCacheTest extends AbstractCache
{

    public function setUp(): void
    {
        $this->cache = new FileCache(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test_cache');
    }

    public function testLoad()
    {
        $this->assertInstanceOf(FileCache::class, $this->cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
}