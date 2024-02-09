<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

class RedisCache implements SimpleCacheInterface
{

    use SerializeTrait, CommonTrait;

    private \Redis $server;
    private string $host;
    private int $port;

    const PORT = 6379;

    public function __construct(string $host)
    {
        if (strpos($host, ':') !== false) {
            list($this->host, $port) = explode(':', $host);
            $this->port = (int) $port;
        } else {
            $this->host = $host;
            $this->port = self::PORT;
        }
        $this->server = new \Redis();
        $this->server->connect($this->host, $this->port);
        //$this->server = new \Redis(['host' => $this->host, 'port' => (int) $this->port]);
    }

    public function get(string $key): mixed
    {
        if ($this->server->exists($key)) {
            $value = $this->server->get($key);
            if (!empty($value) && is_string($value) && $this->isSerialized($value)) {
                $value = unserialize($value);
            }
            return $value;
        }
        return false;
    }

    public function set(string $key, mixed $value, ?int $ttl): bool
    {
        if (is_object($value) || is_array($value)) {
            $value = serialize($value);
        }
        return $this->server->set($key, $value, $this->maxTtl($ttl));
    }

    public function delete(string $key): bool
    {
        if (method_exists($this->server, 'del')) {
            $result = $this->server->del($key);
        } elseif (method_exists($this->server, 'delete')) {
            $result = $this->server->delete($key);
        } elseif (method_exists($this->server, 'unlink')) {
            $result = $this->server->unlink($key);
        }
        return (isset($result) && $result !== false);
    }

    public function flush(): bool
    {
        return $this->server->flushDB();
    }

    public function replace(string $key, mixed $value): bool
    {
        if (is_object($value) || is_array($value)) {
            $value = serialize($value);
        }
        $old = $this->server->getSet($key, $value);
        return ($old !== $value);
    }

    public function touch(string $key, int $ttl): bool
    {
        if (method_exists($this->server, 'expire')) {
            return $this->server->expire($key, $ttl);
        } elseif (method_exists($this->server, 'setTimeOut')) {
            return $this->server->setTimeOut($key, $ttl);
        }
        return false;
    }

    public function getHost(): string
    {
        return $this->host . ":" . $this->port;
    }

    /**
     * @return array<int, int|string> $array
     */
    public function getAllKeys(): array
    {
        return $this->server->keys('*');
    }

    public function increment(string $key, int|float $increment = 1, int $ttl = 0): int|float|bool
    {
        return (is_float($increment)) ? $this->server->incrByFloat($key, $increment) : $this->server->incrBy($key, $increment);
        /*
        if (is_float($increment)) {
            if (!is_numeric($value = $this->get($key))) {
                if (!$this->set($key, $default_value, $ttl)) {
                    return false;
                }
                $value = $default_value;
            }
            $new_value = $value + $increment;
            return $this->replace($key, $new_value) ? $new_value : false;
        }
        */
    }
    public function decrement(string $key, int|float $decrement = 1, int $ttl = 0): int|float|bool
    {
        $value = $this->get($key);
        if (is_float($decrement) || is_float($value) || true) {
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
        } else {
            return $this->server->decrBy($key, $decrement);
        }
        return false;
    }
    public function __destruct()
    {
        $this->server->close();
    }
}