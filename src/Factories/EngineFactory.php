<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Factories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\ApcuCache;
use JuanchoSL\SimpleCache\Repositories\FileCache;
use JuanchoSL\SimpleCache\Repositories\MemCache;
use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Repositories\SessionCache;
use JuanchoSL\SimpleCache\Repositories\YacCache;

class EngineFactory
{
    public static function getInstance(Engines $engine, string $host): SimpleCacheInterface
    {
        return match ($engine) {
            Engines::MEMCACHE => new MemCache($host),
            Engines::MEMCACHED => new MemCached($host),
            Engines::REDIS => new RedisCache($host),
            Engines::FILE => new FileCache($host),
            Engines::SESSION => new SessionCache($host),
            Engines::PROCESS => new ProcessCache($host),
            Engines::YAC => new YacCache($host),
            Engines::APCU => new ApcuCache()
        };
    }
}