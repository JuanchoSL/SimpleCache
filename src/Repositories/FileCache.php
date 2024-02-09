<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

class FileCache implements SimpleCacheInterface
{

    use SerializeTrait, CommonTrait;

    protected string $cache_dir;

    public function __construct(string $host)
    {
        $this->cache_dir = rtrim($host, DIRECTORY_SEPARATOR);
        if (!file_exists($this->cache_dir)) {
            mkdir($this->cache_dir, 0777, true);
        }
    }

    /**
     * @return array{ttl: int, data: mixed}|false
     */
    protected function getContents(string $key): array|bool
    {
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        if (file_exists($cache_file)) {
            $data = file_get_contents($cache_file);
            if (!empty($data)) {
                $data_unserialized = (array) unserialize($data);
                if (isset($data_unserialized['ttl'], $data_unserialized['data']) && is_int($data_unserialized['ttl'])) {
                    $response = [
                        'ttl' => $data_unserialized['ttl'],
                        'data' => $data_unserialized['data']
                    ];
                    if (is_string($data_unserialized['data']) && $this->isSerialized($data_unserialized['data'])) {
                        $response['data'] = unserialize($data_unserialized['data']);
                    }
                    return $response;
                }
            }
        }
        return false;
    }

    /**
     * @param string $key
     * @param array<string,mixed> $data
     */
    protected function putContents(string $key, array $data): bool
    {
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        return file_put_contents($cache_file, serialize($data), LOCK_EX) !== false;
    }

    public function get(string $key): mixed
    {
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        if (file_exists($cache_file)) {
            $data = $this->getContents($key);
            if (is_array($data) && (int) $data['ttl'] > time()) {
                return $data['data'];
            }
            $this->delete($key);
        }
        return false;
    }

    public function set(string $key, mixed $value, ?int $ttl): bool
    {
        if (is_object($value) || is_array($value)) {
            $value = serialize($value);
        }
        $value = ['ttl' => time() + $this->maxTtl($ttl), 'data' => $value];
        return $this->putContents($key, $value);
    }

    public function delete(string $key): bool
    {
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        return unlink($cache_file);
    }

    public function flush(): bool
    {
        $exito = true;
        foreach ($this->getAllKeys() as $key) {
            $exito = ($this->delete($key)) ? $exito : false;
        }
        return $exito;
    }

    public function replace(string $key, mixed $value): bool
    {
        $data = $this->getContents($key);
        if ($data !== false) {
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }
            $value = ['ttl' => $data['ttl'], 'data' => $value];
            return $this->putContents($key, $value);
        }

        return false;
    }

    public function touch(string $key, int $ttl): bool
    {
        if (($value = $this->get($key)) !== false) {
            return $this->set($key, $value, $ttl);
        }
        return false;
    }

    /**
     * @return array<int, string> $array
     */
    public function getAllKeys(): array
    {
        $response = [];
        $files = glob($this->cache_dir . "/*");
        if (!empty($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $response[] = basename($file);
                }
            }
        }
        return $response;
    }

    public function getHost(): string
    {
        return $this->cache_dir;
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
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
        }
        return false;
    }
}