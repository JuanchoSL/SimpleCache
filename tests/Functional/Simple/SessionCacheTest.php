<?php

namespace JuanchoSL\SimpleCache\Tests\Functional\Simple;

use JuanchoSL\SimpleCache\Enums\Engines;

class SessionCacheTest extends AbstractSimpleCache
{

    public function getEngine(): Engines
    {
        return Engines::SESSION;
    }
}