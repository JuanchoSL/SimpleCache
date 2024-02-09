<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Adapters;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;
use Psr\SimpleCache\CacheInterface;

class SimpleCacheAdapter implements CacheInterface
{
    private SimpleCacheInterface $cache;

    public static function getInstance(SimpleCacheInterface $cache): CacheInterface
    {
        return new self($cache);
    }

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

    public function has(string $key): bool
    {
        return ($this->cache->get($key) !== false);
    }

    public function clear(): bool
    {
        return $this->cache->flush();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key) ?? $default;
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $response = [];
        foreach ($keys as $key) {
            $response[$key] = $this->cache->get($key) ?? $default;
        }
        return $response;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        if ($ttl instanceof \DateInterval) {
            $ttl = (int) $ttl->format("%s");
        }
        return $this->cache->set($key, $value, $ttl);
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        if ($ttl instanceof \DateInterval) {
            $ttl = (int) $ttl->format("%s");
        }
        $result = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $result = false;
            }
        }
        return $result;
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
        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $result = false;
            }
        }
        return $result;
    }
}