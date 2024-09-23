<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Contracts;

use Psr\SimpleCache\CacheInterface;

interface SimpleCacheInterface extends CacheInterface
{

    /**
     * Replace the value of an element into cache mantaining the expiring time
     * @param string $key The cache key to replace
     * @param mixed $value The new value to save into cache, can be a number, string, array, string...the value has been serialized before save it
     * @return bool The result of the operation
     */
    public function replace(string $key, mixed $value): bool;

    /**
     * Change the expiring time of a cache value
     * @param string $key The cache key to modify his time to life
     * @param \DateInterval|null|int $ttl New time to live from now if is setted
     * @return bool The result of the operation
     */
    public function touch(string $key, \DateInterval|null|int $ttl): bool;

    /**
     * Get the connection string of repository
     * @return string The connection string of repository
     */
    public function getHost(): string;

    /**
     * Increments a cached value, or create if not exists
     * @param string $key The cache key to increment his value
     * @param int|float $increment The value to increment the actual value
     * @param \DateInterval|null|int $ttl Time to life if the element is created when not exists
     * @return int|float The new value
     */
    public function increment(string $key, int|float $increment = 1, \DateInterval|null|int $ttl = null): int|float|bool;

    /**
     * Decrements a cached value, or create if not exists
     * @param string $key The cache key to decrement his value
     * @param int|float $decrement The value to decrement the actual value
     * @param \DateInterval|null|int $ttl Time to life if the element is created when not exists
     * @return int|float The new value
     */
    public function decrement(string $key, int|float $decrement = 1, \DateInterval|null|int $ttl = null): int|float|bool;

    /**
     * set the max TTL when is not specified on setters methods
     * @param \DateInterval|int $ttl Can be an integer (seconds from now) or a DateInterval instance
     * @return SimpleCacheInterface
     */
    public function setMaxTtl(\DateInterval|int $ttl): static;
}