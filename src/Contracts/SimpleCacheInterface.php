<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Contracts;

interface SimpleCacheInterface
{

    /**
     * Set a value assigned to a key into the cache
     * @param string $key The key name
     * @param mixed $value A value to save into cache, can be a number, string, array, string...the value has been serialized before save it
     * @param int $ttl The number of seconds for save the value
     * @return bool Result of operation, true if value is saved, false otherwise
     */
    public function set(string $key, mixed $value, int $ttl): bool;

    /**
     * Get the value from cache assigned to the key passed
     * @param string $key The key of the cache
     * @return mixed A value from cache, can be a number, string, array, string...false if the value does not exists or is expired
     */
    public function get(string $key): mixed;
    
    /**
     * Delete a value saved into cache
     * @param string $key The cache key to delete
     * @return bool The result of the operation
     */
    public function delete(string $key): bool;
    
    
    /**
     * Truncate all the cache database
     * @return bool The result of the operation
     */
    public function flush(): bool;
    
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
     * @return bool The result of the operation
     */
    public function touch(string $key, int $ttl): bool;

    /**
     * Get the connection string of repository
     * @return string The connection string of repository
     */
    public function getHost(): string;

    /**
     * Increments a cached value, or create if not exists
     * @param string $key The cache key to increment his value
     * @param int|float $increment The value to increment the actual value
     * @param int $ttl Time to life if the element is created when not exists
     * @return int|float The new value
     */
    public function increment(string $key, int|float $increment = 1, int $ttl = 0): int|float|bool;
    
    /**
     * Decrements a cached value, or create if not exists
     * @param string $key The cache key to decrement his value
     * @param int|float $decrement The value to decrement the actual value
     * @param int $ttl Time to life if the element is created when not exists
     * @return int|float The new value
     */
    public function decrement(string $key, int|float $decrement = 1, int $ttl = 0): int|float|bool;
}