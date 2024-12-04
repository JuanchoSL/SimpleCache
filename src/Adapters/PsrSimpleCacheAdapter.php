<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Adapters;

use JuanchoSL\Validators\Types\Strings\StringValidations;
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
        $this->checkKey($key);
        return ($this->cache->get($key) !== false);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->checkKey($key);
        return $this->cache->get($key, $default);
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->checkKeys($keys);
        return $this->cache->getMultiple($keys, $default);
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $this->checkKey($key);
        return $this->cache->set($key, $value, $ttl);
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        $this->checkKeys(array_keys($values));
        return $this->cache->setMultiple($values, $ttl);
    }

    public function delete(string $key): bool
    {
        $this->checkKey($key);
        return $this->cache->delete($key);
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $this->checkKeys($keys);
        return $this->cache->deleteMultiple($keys);
    }

    protected function checkKeys(iterable $keys)
    {
        foreach ($keys as $key) {
            $this->checkKey($key);
        }
    }
    protected function checkKey(string $key)
    {
        if (!(new StringValidations)->isNotEmpty()->isLengthLessOrEqualsThan(64)->isRegex('/^[a-zA-Z0-9_.]+$/')->getResult($key)) {
            throw new \InvalidArgumentException("The key '{$key}' is not valid");
        }
        return true;
    }
}