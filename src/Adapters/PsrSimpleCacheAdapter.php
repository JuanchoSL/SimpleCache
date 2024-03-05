<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Adapters;

use Psr\SimpleCache\CacheInterface;

class PsrSimpleCacheAdapter implements CacheInterface
{
    private CacheInterface $cache;

    public static function getInstance(CacheInterface $cache): CacheInterface
    {
        return new self($cache);
    }

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function has(string $key): bool
    {
        return ($this->cache->get($key) !== false);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }
    
    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->cache->getMultiple($keys, $default);
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }
    
    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        return $this->cache->setMultiple($values, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }
    
    /**
     * @param iterable<int, string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }
}