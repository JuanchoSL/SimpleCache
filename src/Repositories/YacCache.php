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

    public function getHost(): string
    {
        return $this->prefix;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        $keysFound = $this->server->dump();
        return array_column($keysFound, "key");
    }

    public function clear(): bool
    {
        $result = $this->server->flush();
        $this->log("Cleared cache {prefix}", LogLevel::DEBUG, ['prefix' => $this->getHost(), 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->checkKey($key);
        $result = $this->server->{$key};
        if ($result === null) {
            $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
            $result = (is_callable($default)) ? $default() : $default;
        }
        return $result;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $ttl = $this->maxTtl($ttl);
        if ($ttl > 0) {
            $this->checkKey($key);
            $result = $this->server->set($key, $value, $ttl);
            $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result;
        } else {
            return $this->delete($key);
        }
    }

    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            $result = $this->server->delete($key, -1);
            $this->log("The key {key} is going to delete", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result;
        }
        $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        return false;
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->checkKeys($keys);
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
        $this->checkKeys($keys);
        return $this->server->delete($keys, -1);
    }

}