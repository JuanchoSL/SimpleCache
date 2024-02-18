<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\FileCache;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;

class FileCacheTest extends AbstractSimpleCache
{

    public function setUp(): void
    {
        $this->cache = new PsrSimpleCacheAdapter(new FileCache(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test_cache'));
    }
}