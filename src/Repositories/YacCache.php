<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\ServiceUnavailableException;
use Psr\Log\LogLevel;

class YacCache extends AbstractCache
{

    private \Yac $server;
    private string $prefix;
    public function __construct(string $prefix)
    {
        if (!extension_loaded('yac')) {
            throw new ServiceUnavailableException("The extension Yac is not available");
        }
        $this->prefix = $prefix;
        $this->server = new \Yac($this->prefix);
    }
    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $result = $this->server->set($key, $value, $this->maxTtl($ttl));
        $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
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
        return $this->prefix;
    }

    public function delete(string $key): bool
    {
        $result = $this->server->delete($key, -1);
        $this->log("The key {key} is going to delete", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function clear(): bool
    {
        return $this->server->flush();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $result = $this->server->{$key};
        if ($result === null) {
            $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
            $result = (is_callable($default)) ? $default() : $default;
        }
        return $result;
    }

    public function replace(string $key, mixed $value): bool
    {
        $result = $this->set($key, $value);
        $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['new' => $value], 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        $keysFound = $this->server->dump();
        return array_column($keysFound, "key");
    }



    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $this->maxTtl($ttl));
        }
        return true;
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $default_value = $default;
        $results = $this->server->get((array) $keys);
        $results = array_filter($results);
        if (is_array($results) and count($results) < count($keys)) {
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
        return $this->server->delete($keys, -1);
    }

}