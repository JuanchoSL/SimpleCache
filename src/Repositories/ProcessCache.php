<?php

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

class ProcessCache implements SimpleCacheInterface
{

    /**
     * @var array<string, array<string, array<string, mixed>>> $cache
     */
    private static array $cache = [];
    protected string $hostName = 'session_cache';

    public function __construct(string $index)
    {
        $this->hostName = $index;
        self::$cache[$this->hostName] = array();
    }

    public function get(string $key): mixed
    {
        if (array_key_exists($key, self::$cache[$this->hostName])) {
            $value = self::$cache[$this->hostName][$key];
            if (isset($value['ttl'], $value['value'])) {
                if ($value['ttl'] > time()) {
                    return $value['value'];
                } else {
                    $this->delete($key);
                }
            }
        }
        return false;
    }

    public function set(string $key, mixed $value, int $ttl): bool
    {
        if (empty($ttl)) {
            $ttl = 3600 * 24 * 30;
        }
        self::$cache[$this->hostName][$key] = array('ttl' => time() + $ttl, 'value' => $value);
        return (isset(self::$cache[$this->hostName][$key]));
    }

    public function delete(string $key): bool
    {
        if (isset(self::$cache[$this->hostName]) && array_key_exists($key, self::$cache[$this->hostName])) {
            unset(self::$cache[$this->hostName][$key]);
        }
        return true;
    }

    public function flush(): bool
    {
        unset(self::$cache[$this->hostName]);
        return !array_key_exists($this->hostName, self::$cache);
    }

    public function replace(string $key, mixed $value): bool
    {
        if (array_key_exists($key, self::$cache[$this->hostName])) {
            self::$cache[$this->hostName][$key]['value'] = $value;
            return true;
        }
        return false;
    }

    public function touch(string $key, int $ttl): bool
    {
        if (($value = $this->get($key)) !== false) {
            return $this->set($key, $value, $ttl);
        }
        return false;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        return array_keys(self::$cache[$this->hostName]);
    }

    public function getHost(): string
    {
        return $this->hostName;
    }
    public function increment(string $key, int|float $increment = 1, int $ttl = 0): int|float|false
    {
        $value = $this->get($key);
        if (!$value) {
            if ($this->set($key, $increment, $ttl)) {
                return $increment;
            }
        } else {
            $new_value = $value + $increment;
            if ($this->replace($key, $new_value))
                return $new_value;
        }
        return false;
    }
    public function decrement(string $key, int|float $decrement = 1, int $ttl = 0): int|float|false
    {
        $value = $this->get($key);
        if (!$value) {
            $decrement *= -1;
            if ($this->set($key, $decrement, $ttl)) {
                return $decrement;
            }
        } else {
            $new_value = $value - $decrement;
            if ($this->replace($key, $new_value)){
                return $new_value;
            }
        }
        return false;
    }
}