<?php

namespace JuanchoSL\SimpleCache\Tests\Common;

use JuanchoSL\SimpleCache\Repositories\FileCache;
use JuanchoSL\SimpleCache\Repositories\MemCache;
use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Repositories\SessionCache;

class CacheFactory
{
    public static function getInstance(CacheEnum $type)
    {
        switch ($type) {
            case CacheEnum::FILE:
                return new FileCache(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test_cache');

            case CacheEnum::MEMCACHE:
                return new MemCache($_ENV['MEMCACHE_HOST']);

            case CacheEnum::MEMCACHED:
                return new MemCached($_ENV['MEMCACHE_HOST']);

            case CacheEnum::PROCESS:
                return new ProcessCache('test_cache');

            case CacheEnum::REDIS:
                return new RedisCache($_ENV['REDIS_HOST']);

            case CacheEnum::SESSION:
                return new SessionCache('test_cache');
        }
    }
}