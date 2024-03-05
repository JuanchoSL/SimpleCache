<?php

namespace JuanchoSL\SimpleCache\Tests\Functional\Simple;

use JuanchoSL\SimpleCache\Tests\Common\CacheEnum;
use JuanchoSL\SimpleCache\Tests\Common\CacheFactory;

class FileCacheTest extends AbstractSimpleCache
{

    public function getCacheType()
    {
        return CacheFactory::getInstance(CacheEnum::FILE);
    }
}