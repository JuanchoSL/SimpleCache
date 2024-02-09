<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

class ProcessCache implements SimpleCacheInterface
{
    use CommonTrait;
    /**
     * @var array<string, array<string, array<string, mixed>>> $cache
     */
    private static array $cache = [];
    protected string $host_name = 'session_cache';

    public function __construct(string $index)
    {
        $this->host_name = $index;
        self::$cache[$this->host_name] = array();
    }

    public function get(string $key): mixed
    {
        if (array_key_exists($key, self::$cache[$this->host_name])) {
            $value = self::$cache[$this->host_name][$key];
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

    public function set(string $key, mixed $value, ?int $ttl): bool
    {
        self::$cache[$this->host_name][$key] = array('ttl' => time() + $this->maxTtl($ttl), 'value' => $value);
        return (isset(self::$cache[$this->host_name][$key]));
    }

    public function delete(string $key): bool
    {
        if (isset(self::$cache[$this->host_name]) && array_key_exists($key, self::$cache[$this->host_name])) {
            unset(self::$cache[$this->host_name][$key]);
        }
        return true;
    }

    public function flush(): bool
    {
        unset(self::$cache[$this->host_name]);
        return !array_key_exists($this->host_name, self::$cache);
    }

    public function replace(string $key, mixed $value): bool
    {
        if (array_key_exists($key, self::$cache[$this->host_name])) {
            self::$cache[$this->host_name][$key]['value'] = $value;
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
        return array_keys(self::$cache[$this->host_name]);
    }

    public function getHost(): string
    {
        return $this->host_name;
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
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
        }
        return false;
    }
}