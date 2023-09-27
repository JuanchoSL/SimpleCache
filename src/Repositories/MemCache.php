<?php

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;
use Exception;

class MemCache implements SimpleCacheInterface
{

    private \Memcache $server;
    private string $host;
    private int $port;

    const PORT = 11211;

    public function __construct(string $host)
    {
        if (strpos($host, ':') !== false) {
            list($this->host, $port) = explode(':', $host);
            $this->port = (int) $port;
        } else {
            $this->host = $host;
            $this->port = self::PORT;
        }
        $this->server = new \Memcache();
        $this->server->connect($this->host, $this->port);
    }

    public function set(string $key, mixed $value, int $ttl): bool
    {
        return $this->server->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
    }

    public function touch(string $key, int $ttl): bool
    {
        if (($value = $this->get($key)) !== false) {
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
        return $this->server->delete($key);
    }

    public function flush(): bool
    {
        return $this->server->flush();
    }

    public function get(string $key): mixed
    {
        return $this->server->get($key);
    }

    public function replace(string $key, mixed $value): bool
    {
        return $this->server->replace($key, $value);
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

    public function increment(string $key, int|float $increment = 1, int $ttl = 0): int|float|false
    {
        $value = $this->get($key);
        if (!$value) {
            if ($this->set($key, $increment, $ttl)) {
                return $increment;
            }
        } else {
            $new_value = $value + $increment;
            if ($this->replace($key, $new_value)){
                return $new_value;
            }
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