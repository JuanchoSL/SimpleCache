<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\Exceptions\ServiceUnavailableException;
use Psr\Log\LogLevel;

class MemCache extends AbstractCache
{

    private \Memcache $server;
    private string $host;
    private int $port;

    const PORT = 11211;

    public function __construct(string $host)
    {
        if (!extension_loaded('memcache')) {
            throw new ServiceUnavailableException("The extension Memcache is not available");
        }
        if (strpos($host, ':') !== false) {
            list($this->host, $port) = explode(':', $host);
            $this->port = (int) $port;
        } else {
            $this->host = $host;
            $this->port = static::PORT;
        }
        $this->server = new \Memcache();
        if (!$this->server->connect($this->host, $this->port)) {
            $exception = new DestinationUnreachableException("Can not connect to the required destiny");
            $this->log($exception, LogLevel::ERROR, [
                'exception' => $exception,
                'credentials' => [
                    'host' => $this->host,
                    'port' => $this->port
                ]
            ]);
            throw $exception;
        }
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $result = $this->server->set($key, $value, MEMCACHE_COMPRESSED, $this->maxTtl($ttl));
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
        return $this->host . ":" . $this->port;
    }

    public function delete(string $key): bool
    {
        $result = $this->server->delete($key);
        $this->log("The key {key} is going to delete", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function clear(): bool
    {
        return $this->server->flush();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $result = $this->server->get($key);
        if ($result === false) {
            $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
            $result = $default;
        }
        return $result;
    }

    public function replace(string $key, mixed $value): bool
    {
        $result = $this->server->replace($key, $value);
        $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['new' => $value], 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        $keysFound = [];
        $slabs = $this->server->getExtendedStats('slabs');
        foreach ($slabs as $serverSlabs) {
            foreach ($serverSlabs as $slabId => $slabMeta) {
                if (!is_numeric($slabId))
                    continue;
                $cacheDump = $this->server->getExtendedStats('cachedump', (int) $slabId);
                foreach ($cacheDump as $dump) {
                    if (!is_array($dump))
                        continue;
                    foreach ($dump as $key => $value) {
                        $keysFound[] = $key;
                    }
                }
            }
        }
        return $keysFound;
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
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
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
            if ($this->replace($key, $new_value))
                return $new_value;
        }
        return false;
    }

    public function __destruct()
    {
        $this->server->close();
    }
}