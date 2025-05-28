<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\Validators\Types\Integers\IntegerValidation;
use JuanchoSL\Validators\Types\Strings\StringValidations;
use Psr\Log\LogLevel;

class FileCache extends AbstractCache
{

    protected string $cache_dir;

    public function __construct(string $host)
    {
        $this->cache_dir = rtrim($host, DIRECTORY_SEPARATOR);
        if (!file_exists($this->cache_dir)) {
            if (!mkdir($this->cache_dir, 0777, true)) {
                $exception = new DestinationUnreachableException("Can not connect to the required destiny");
                $this->log($exception, LogLevel::ERROR, [
                    'exception' => $exception,
                    'credentials' => [
                        'host' => $this->cache_dir
                    ]
                ]);
                throw $exception;
            }
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
                if (isset($data_unserialized['ttl'], $data_unserialized['data']) && IntegerValidation::is($data_unserialized['ttl'])) {
                    $response = [
                        'ttl' => $data_unserialized['ttl'],
                        'data' => $data_unserialized['data']
                    ];
                    if ((new StringValidations)->is()->isNotEmpty()->isSerialized()->getResult($data_unserialized['data'])) {
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

    public function get(string $key, mixed $default = null): mixed
    {
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        if (file_exists($cache_file)) {
            $data = $this->getContents($key);
            if (is_array($data) && IntegerValidation::isValueGreatherThan($data['ttl'], time())) {
                return $data['data'];
            }
            $this->log("The key {key} is not valid", LogLevel::INFO, ['key' => $key, 'data' => $data, 'method' => __FUNCTION__]);
            $this->delete($key);
        } else {
            $this->log("The file {cache_file} does not exists", LogLevel::INFO, ['cache_file' => $cache_file, 'method' => __FUNCTION__]);
        }
        return $default;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        if (is_object($value) || is_array($value)) {
            $value = serialize($value);
        }
        $value = ['ttl' => time() + $this->maxTtl($ttl), 'data' => $value];
        $result = $this->putContents($key, $value);
        $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function delete(string $key): bool
    {
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        $result = (file_exists($cache_file)) ? unlink($cache_file) : false;
        $this->log("The file {key} is going to delete", LogLevel::INFO, ['key' => $cache_file, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function clear(): bool
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
            $result = $this->putContents($key, $value);
            $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['old' => $data['data'], 'new' => $value], 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result;
        }
        return false;
    }

    public function touch(string $key, \DateInterval|null|int $ttl): bool
    {
        if (($value = $this->get($key)) !== null) {
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
    public function increment(string $key, int|float $increment = 1, \DateInterval|null|int $ttl = null): int|float|false
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
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
        }
        return false;
    }
}