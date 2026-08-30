<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\DestinationUnreachableException;
use Psr\Log\LogLevel;

class ProcessCache extends AbstractCache
{

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
            $exception = new DestinationUnreachableException("Can not connect to the required destiny");
            $this->log($exception, LogLevel::ERROR, [
                'exception' => $exception,
                'credentials' => [
                    'host' => $this->host_name
                ]
            ]);
            throw $exception;
        }
    }

    public function getHost(): string
    {
        return $this->host_name;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        return array_keys(static::$cache[$this->host_name]);
    }

    public function clear(): bool
    {
        static::$cache[$this->host_name] = [];
        $result = empty(static::$cache[$this->host_name]);
        $this->log("Cleared cache {prefix}", LogLevel::DEBUG, ['prefix' => $this->getHost(), 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function has(string $key): bool
    {
        $this->checkKey($key);
        return (isset(static::$cache[$this->host_name]) && array_key_exists($key, static::$cache[$this->host_name]) && static::$cache[$this->host_name][$key]['ttl'] > time());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->checkKey($key);
        if ($this->has($key)) {
            $value = static::$cache[$this->host_name][$key];
            return $value['value'];
            if (isset($value['ttl'], $value['value']) && $value['ttl'] > time()) {
                return $value['value'];
            }
            $this->log("The key {key} is not valid", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__]);
            $this->delete($key);
        } else {
            $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        }
        return (is_callable($default)) ? $default() : $default;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $ttl = $this->maxTtl($ttl);
        if ($ttl > 0) {
            $this->checkKey($key);
            static::$cache[$this->host_name][$key] = array('ttl' => time() + $ttl, 'value' => $value);
            $result = (isset(static::$cache[$this->host_name][$key]));
            $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result;
        } else {
            return $this->delete($key);
        }
    }

    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            $this->log("The key {key} is going to delete", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
            unset(static::$cache[$this->host_name][$key]);
            return true;
        }
        $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        return false;
    }

    public function replace(string $key, mixed $value): bool
    {
        $this->checkKey($key);
        if ($this->has($key)) {
            $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['old' => static::$cache[$this->host_name][$key]['value'], 'new' => $value], 'method' => __FUNCTION__]);
            static::$cache[$this->host_name][$key]['value'] = $value;
            return true;
        }
        $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        return false;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $this->checkKeys($keys);
        $result = array_diff_key(static::$cache[$this->host_name], array_fill_keys($keys, null));
        $counter = count(static::$cache[$this->host_name]) - count($result);
        $this->log("Some keys are going to be deleted", LogLevel::INFO, ['keys' => $keys, 'method' => __FUNCTION__, 'result' => $counter]);
        static::$cache[$this->host_name] = $result;
        return $counter == count($keys);
    }
}