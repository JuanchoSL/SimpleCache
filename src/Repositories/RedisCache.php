<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\Exceptions\ServiceUnavailableException;
use JuanchoSL\Validators\Types\Strings\StringValidations;
use Psr\Log\LogLevel;

class RedisCache extends AbstractCache
{

    private \Redis $server;
    private string $host;
    private int $port;

    const PORT = 6379;

    public function __construct(string $host)
    {
        if (!extension_loaded('redis')) {
            throw new ServiceUnavailableException("The extension Redis is not available");
        }
        if (strpos($host, ':') !== false) {
            list($this->host, $port) = explode(':', $host);
            $this->port = (int) $port;
        } else {
            $this->host = $host;
            $this->port = static::PORT;
        }
        $this->server = new \Redis();
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

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->server->exists($key)) {
            $value = $this->server->get($key);
            if ((new StringValidations)->is()->isNotEmpty()->isSerialized()->getResult($value)) {
                $value = unserialize($value);
            }
            return $value;
        }
        $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        return $default;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        if (is_object($value) || is_array($value)) {
            $value = serialize($value);
        }
        $result = $this->server->set($key, $value, $this->maxTtl($ttl));
        $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function delete(string $key): bool
    {
        return $this->deleteMultiple([$key]);
    }
    
    public function deleteMultiple(iterable $keys): bool
    {
        if (method_exists($this->server, 'del')) {
            $result = $this->server->del($keys);
        } elseif (method_exists($this->server, 'delete')) {
            $result = $this->server->delete($keys);
        } elseif (method_exists($this->server, 'unlink')) {
            $result = $this->server->unlink($keys);
        }
        $result = (isset($result) && $result !== false);
        $this->log("Some keys are going to be deleted", LogLevel::INFO, ['keys' => $keys, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function clear(): bool
    {
        return $this->server->flushDB();
    }

    public function replace(string $key, mixed $value): bool
    {
        if (is_object($value) || is_array($value)) {
            $value = serialize($value);
        }
        $old = $this->server->getSet($key, $value);
        $result = ($old !== $value);
        $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['old' => $old, 'new' => $value], 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function touch(string $key, \DateInterval|null|int $ttl): bool
    {
        if (method_exists($this->server, 'expire')) {
            return $this->server->expire($key, $this->maxTtl($ttl));
        } elseif (method_exists($this->server, 'setTimeOut')) {
            return $this->server->setTimeOut($key, $this->maxTtl($ttl));
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

    public function increment(string $key, int|float $increment = 1, \DateInterval|null|int $ttl = null): int|float|bool
    {
        return (is_float($increment)) ? $this->server->incrByFloat($key, $increment) : $this->server->incrBy($key, $increment);
    }

    public function decrement(string $key, int|float $decrement = 1, \DateInterval|null|int $ttl = null): int|float|bool
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
            //return $this->server->decrBy($key, $decrement);
        }
        return false;
    }
    public function __destruct()
    {
        $this->server->close();
    }
}