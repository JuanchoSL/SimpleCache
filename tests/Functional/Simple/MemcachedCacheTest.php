<?php

namespace JuanchoSL\SimpleCache\Tests\Functional\Simple;

use JuanchoSL\SimpleCache\Enums\Engines;

class MemcachedCacheTest extends AbstractSimpleCache
{

    public function getEngine(): Engines
    {
        return Engines::MEMCACHED;
    }
}