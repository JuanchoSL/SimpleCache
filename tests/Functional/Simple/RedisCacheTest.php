<?php

namespace JuanchoSL\SimpleCache\Tests\Functional\Simple;

use JuanchoSL\SimpleCache\Enums\Engines;

class RedisCacheTest extends AbstractSimpleCache
{

    public function getEngine(): Engines
    {
        return Engines::REDIS;
    }
}