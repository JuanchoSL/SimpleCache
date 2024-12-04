<?php

namespace JuanchoSL\SimpleCache\Tests\Common;

use JuanchoSL\SimpleCache\Enums\Engines;

class Credentials
{

    const GIT_MODE = true;

    public static function getHost(Engines $type): string
    {
        return match ($type) {
            Engines::MEMCACHE => $_ENV['MEMCACHE_HOST'],
            Engines::MEMCACHED => $_ENV['MEMCACHE_HOST'],
            Engines::REDIS => $_ENV['REDIS_HOST'],
            Engines::FILE => dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test_cache',
            Engines::SESSION => 'test_cache',
            Engines::PROCESS => 'test_cache'
        };
    }
}