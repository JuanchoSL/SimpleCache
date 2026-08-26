<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\Exceptions\ServiceUnavailableException;
use Psr\Log\LogLevel;

class MemCached extends AbstractCache
{

    private \Memcached $server;
    private string $host;
    private int $port;

    const PORT = 11211;

    public function __construct(string $host)
    {
        if (!extension_loaded('memcached')) {
            throw new ServiceUnavailableException("The extension Memcached is not available");
        }
        if (strpos($host, ':') !== false) {
            list($this->host, $port) = explode(':', $host);
            $this->port = (int) $port;
        } else {
            $this->host = $host;
            $this->port = static::PORT;
        }
        $this->server = new \Memcached();
        if (!$this->server->addServer($this->host, $this->port)) {
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
        $this->server->setOption(\Memcached::OPT_BINARY_PROTOCOL, false);
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
    public function clear(): bool
    {
        $result = $this->server->flush();
        $this->log("Cleared cache {prefix}", LogLevel::DEBUG, ['prefix' => $this->getHost(), 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->checkKey($key);
        $result = $this->server->get($key);
        if ($this->server->getResultCode() == \Memcached::RES_NOTFOUND) {
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
        //$this->checkKey($key);
        if ($this->has($key)) {
            $result = $this->server->delete($key);
            $this->log("The key {key} is going to delete", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result;
        }
        $this->log("The key {key} does not exists", LogLevel::INFO, ['key' => $key, 'method' => __FUNCTION__]);
        return false;
    }
    public function touch(string $key, \DateInterval|null|int $ttl): bool
    {
        $ttl = $this->maxTtl($ttl);
        if ($ttl > 0) {
            $this->checkKey($key);
            return $this->server->touch($key, $ttl);
        } else {
            return $this->delete($key);
        }
    }

    public function replace(string $key, mixed $value): bool
    {
        $this->checkKey($key);
        $result = $this->server->replace($key, $value);
        $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['new' => $value], 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function setMultiple(iterable $keys, \DateInterval|null|int $ttl = null): bool
    {
        $this->checkKeys($keys);
        return $this->server->setMulti((array) $keys, $this->maxTtl($ttl));
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->checkKeys($keys);
        $default_value = $default;
        $results = $this->server->getMulti((array) $keys);
        if (is_array($results)) {
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
        $results = $this->server->deleteMulti((array) $keys);
        $fails = 0;
        foreach ($results as $result) {
            if ($result !== true) {
                $fails++;
            }
        }
        $this->log("Some keys are going to be deleted", LogLevel::DEBUG, ['keys' => $keys, 'method' => __FUNCTION__, 'result' => ['ok' => count($keys) - $fails, 'ko' => $fails]]);
        return ($fails === 0);
    }

    public function __destruct()
    {
        $this->server->quit();
    }
}