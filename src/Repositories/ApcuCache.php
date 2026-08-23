<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\ServiceUnavailableException;
use Psr\Log\LogLevel;

class ApcuCache extends AbstractCache
{

    public function __construct()
    {
        if (!extension_loaded('apcu')) {
            throw new ServiceUnavailableException("The extension Apcu is not available");
        }
    }

    public function has(string $key): bool
    {
        return !empty(apcu_exists($key));
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $result = apcu_store($key, $value, $this->maxTtl($ttl));
        $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return ($result === true);
    }

    public function touch(string $key, \DateInterval|null|int $ttl): bool
    {
        if (($value = $this->get($key)) !== null) {
            return $this->set($key, $value, $ttl);
        }
        return false;
    }

    public function getHost(): string
    {
        return '';
    }

    public function delete(string $key): bool
    {
        $result = apcu_delete($key);
        $this->log("The key {key} is going to delete", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return ($result === true);
    }

    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $result = apcu_fetch($key, $success);
        if ($result === false) {
            $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
            $result = (is_callable($default)) ? $default() : $default;
        }
        return $result;
    }

    public function replace(string $key, mixed $value): bool
    {

        $ttl = ($this->has($key)) ? apcu_key_info($key)['ttl'] : $this->maxTtl();
        $result = $this->set($key, $value, $ttl);
        $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['new' => $value], 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        $keysFound = apcu_cache_info();
        return array_column($keysFound, "cache_list");
    }

    public function increment(string $key, int|float $increment = 1, \DateInterval|null|int $ttl = null): int|float|false
    {

        if (!apcu_exists($key)) {
            if ($this->set($key, $increment, $ttl)) {
                return $increment;
            }
        } elseif (false && is_integer($increment)) {
            return apcu_inc($key, $increment, $success);
        } else {
            $value = $this->get($key);
            $new_value = $value + $increment;
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
        }
        return false;
    }
    public function decrement(string $key, int|float $decrement = 1, \DateInterval|null|int $ttl = null): int|float|false
    {
        if (!apcu_exists($key)) {
            $decrement *= -1;
            if ($this->set($key, $decrement, $ttl)) {
                return $decrement;
            }
        } elseif (false && is_integer($decrement)) {
            return apcu_dec($key, $decrement);
        } else {
            $value = $this->get($key);
            $new_value = $value - $decrement;
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
        }
        return false;
    }

    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        $result = apcu_store((array) $values, null, $this->maxTtl($ttl));
        return ($result === true or (is_array($result) && empty($result)));
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $default_value = $default;
        $results = apcu_fetch((array) $keys, $success);
        if (!$results or (is_array($results) and count($results) < count($keys))) {
            $i = 0;
            foreach ($keys as $key) {
                if (empty($results[$key]) && !is_null($default)) {
                    if (is_iterable($default)) {
                        $default_value = (array_key_exists($key, $default)) ? $default[$key] : $default[$i];
                    }
                    $results[$key] = (is_callable($default_value)) ? $default_value() : $default_value;
                }
                $i += 1;
            }
        }
        return $results;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $result = apcu_delete($keys);
        return ($result === true or (is_array($result) && empty($result)));
    }

}