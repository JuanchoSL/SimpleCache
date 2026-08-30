<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\DestinationUnreachableException;
use Psr\Log\LogLevel;

class SessionCache extends AbstractCache
{
    protected string $host_name = 'session_cache';

    public function __construct(string $index)
    {
        $this->host_name = $index;
        if (session_status() !== PHP_SESSION_ACTIVE /*&& !headers_sent()*/)
            session_start();
        if (empty($_SESSION) || !array_key_exists($this->host_name, $_SESSION)) {
            $_SESSION[$this->host_name] = array();
        }
        if (!isset($_SESSION[$this->host_name])) {
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
        return array_keys($_SESSION[$this->host_name]);
    }
    public function clear(): bool
    {
        $_SESSION[$this->host_name] = [];
        $result = empty($_SESSION[$this->host_name]);
        $this->log("Cleared cache {prefix}", LogLevel::DEBUG, ['prefix' => $this->getHost(), 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function has(string $key): bool
    {
        $this->checkKey($key);
        return (array_key_exists($key, $_SESSION[$this->host_name]) && $_SESSION[$this->host_name][$key]['ttl'] > time());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->checkKey($key);
        if ($this->has($key)) {
            return $_SESSION[$this->host_name][$key]['value'];
        } else {
            $this->delete($key);
            $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        }
        return (is_callable($default)) ? $default() : $default;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $ttl = $this->maxTtl($ttl);
        if ($ttl > 0) {
            $this->checkKey($key);
            $_SESSION[$this->host_name][$key] = array('ttl' => time() + $ttl, 'value' => $value);
            $result = (isset($_SESSION[$this->host_name][$key]));
            $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result;
        } else {
            return $this->delete($key);
        }
    }

    public function delete(string $key): bool
    {
        $this->checkKey($key);
        if ($this->has($key)) {
            $this->log("The key {key} is going to delete", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
            unset($_SESSION[$this->host_name][$key]);
            return true;
        }
        $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        return false;
    }

    public function replace(string $key, mixed $value): bool
    {
        $this->checkKey($key);
        if ($this->has($key)) {
            $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['old' => $_SESSION[$this->host_name][$key]['value'], 'new' => $value], 'method' => __FUNCTION__]);
            $_SESSION[$this->host_name][$key]['value'] = $value;
            return true;
        }
        $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        return false;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $this->checkKeys($keys);
        $result = array_diff_key($_SESSION[$this->host_name], array_fill_keys($keys, null));
        $counter = count($_SESSION[$this->host_name]) - count($result);
        $this->log("Some keys are going to be deleted", LogLevel::INFO, ['keys' => $keys, 'method' => __FUNCTION__, 'result' => $counter]);
        $_SESSION[$this->host_name] = $result;
        return $counter == count($keys);
    }
}