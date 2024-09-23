<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

class ProcessCache extends AbstractCache
{
    use CommonTrait;
    /**
     * @var array<string, array<string, array<string, mixed>>> $cache
     */
    private static array $cache = [];
    protected string $host_name = 'process_cache';

    public function __construct(string $index)
    {
        $this->host_name = $index;
        static::$cache[$this->host_name] = array();
        if (!isset(static::$cache[$this->host_name])) {
            $exception = new \Exception("Can not connect to the required server");
            $this->log($exception, 'error', [
                'exception' => $exception,
                'credentials' => [
                    'host' => $this->host_name
                ]
            ]);
            throw $exception;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$cache[$this->host_name])) {
            $value = static::$cache[$this->host_name][$key];
            if (isset($value['ttl'], $value['value']) && $value['ttl'] > time()) {
                return $value['value'];
            }
            $this->log("The key {key} is not valid", 'info', ['key' => $key, 'data' => $value, 'method' => __FUNCTION__]);
            $this->delete($key);
        } else {
            $this->log("The key {key} does not exists", 'info', ['key' => $key, 'method' => __FUNCTION__]);
        }
        return $default;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        static::$cache[$this->host_name][$key] = array('ttl' => time() + $this->maxTtl($ttl), 'value' => $value);
        $result = (isset(static::$cache[$this->host_name][$key]));
        $this->log("The key {key} is going to save", 'info', ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function delete(string $key): bool
    {
        if (isset(static::$cache[$this->host_name]) && array_key_exists($key, static::$cache[$this->host_name])) {
            $this->log("The key {key} is going to delete", 'info', ['key' => $key, 'method' => __FUNCTION__]);
            unset(static::$cache[$this->host_name][$key]);
            return true;
        }
        return false;
    }

    public function clear(): bool
    {
        static::$cache[$this->host_name] = [];
        return empty(static::$cache[$this->host_name]);
    }

    public function replace(string $key, mixed $value): bool
    {
        if (array_key_exists($key, static::$cache[$this->host_name])) {
            $this->log("The key {key} is going to be replaced", 'info', ['key' => $key, 'data' => ['old' => static::$cache[$this->host_name][$key]['value'], 'new' => $value], 'method' => __FUNCTION__]);
            static::$cache[$this->host_name][$key]['value'] = $value;
            return true;
        }
        $this->log("The key {key} does not exists", 'info', ['key' => $key, 'method' => __FUNCTION__]);
        return false;
    }

    public function touch(string $key, \DateInterval|null|int $ttl): bool
    {
        if (($value = $this->get($key)) !== null) {
            return $this->set($key, $value, $ttl);
        }
        return false;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        return array_keys(static::$cache[$this->host_name]);
    }

    public function getHost(): string
    {
        return $this->host_name;
    }
    public function increment(string $key, int|float $increment = 1, \DateInterval|null|int $ttl = null): int|float|false
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
    public function decrement(string $key, int|float $decrement = 1, \DateInterval|null|int $ttl = null): int|float|false
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