<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Adapters;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

class SimpleCacheAdapter
{
    private SimpleCacheInterface $cache;

    public function __construct(SimpleCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $function
     * @param array<int,mixed> $params
     */
    public function __call(string $function, array $params = []): mixed
    {
        return call_user_func_array([$this->cache, $function], $params);
    }
}