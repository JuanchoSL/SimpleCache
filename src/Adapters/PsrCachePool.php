<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Adapters;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class PsrCachePool implements CacheItemPoolInterface
{
    /**
     * @var array<string,CacheItemInterface> $deferred
     */
    private array $deferred = [];

    private CacheInterface $cache;

    public static function getInstance(CacheInterface $cache): CacheItemPoolInterface
    {
        return new self($cache);
    }

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function clear(): bool
    {
        $this->deferred = [];
        return $this->cache->clear();
    }

    public function deleteItem(string $key): bool
    {
        if (array_key_exists($key, $this->deferred)) {
            unset($this->deferred[$key]);
        }
        return $this->cache->delete($key);
    }

    public function deleteItems(array $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                $result = false;
            }
        }
        return $result;
    }

    public function getItem(string $key): CacheItemInterface
    {
        if (array_key_exists($key, $this->deferred)) {
            return $this->deferred[$key];
        } elseif ($this->cache->has($key)) {
            $return = $this->cache->get($key);
            if ($return instanceof PsrCacheItem) {
                return $return;
            }
        }
        return new PsrCacheItem($key);
    }

    /**
     * @param array<int, string> $keys
     * @return array<string,CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        $response = [];
        foreach ($keys as $key) {
            $response[$key] = $this->getItem($key);
        }
        return $response;
    }

    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function save(CacheItemInterface $item): bool
    {
        $ttl = $item->getExpirationTimestamp() - time();
        if ($ttl < 0) {
            return false;
        }
        return $this->cache->set($item->getKey(), $item, $ttl);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;
        return true;
    }

    public function commit(): bool
    {
        $result = true;
        foreach ($this->deferred as $key => $item) {
            if ($this->save($item)) {
                unset($this->deferred[$key]);
            } else {
                $result = false;
            }
        }
        return $result;
    }
    public function __destruct()
    {
        $this->commit();
    }
}