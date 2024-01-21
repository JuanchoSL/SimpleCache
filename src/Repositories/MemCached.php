<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

class MemCached implements SimpleCacheInterface
{

    private \Memcached $server;
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
        $this->server = new \Memcached();
        $this->server->addServer($this->host, $this->port);
        $this->server->setOption(\Memcached::OPT_BINARY_PROTOCOL, false);
    }

    public function set(string $key, mixed $value, int $ttl): bool
    {
        return $this->server->set($key, $value, $ttl);
    }

    public function touch(string $key, int $ttl): bool
    {
        return $this->server->touch($key, $ttl);
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
        /*
        $return = $this->server->getAllKeys();
        if (empty($return)) {
            $return = [];
        }
        return $return;
        
        print_r($this->server->getVersion());
        exit;
        */
        $keysFound = [];
        $slabs = $this->server->getStats();
        //$items = $this->server->getStats('items');
        foreach ($slabs as $serverSlabs) {
            //foreach ($serverSlabs as $slabId => $slabMeta) {
            $cacheDump = $this->server->getStats("cachedump 1 0");
            foreach ($cacheDump as $dump) {
                if (!is_array($dump))
                    continue;
                foreach ($dump as $key => $value) {
                    $keysFound[] = $key;
                }
            }
            //}
        }
        return $keysFound;
        /*
                $keys = $this->server->getAllKeys();
                $this->server->getDelayed($keys);
                return $this->server->fetchAll();
                */
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
    public function __destruct()
    {
        $this->server->quit();
    }
}